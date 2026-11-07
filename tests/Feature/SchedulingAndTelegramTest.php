<?php

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\Table;
use App\Models\User;
use App\Services\TelegramNotifier;
use Database\Seeders\DefaultTranslationsSeeder;
use Database\Seeders\TableSeeder;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->seed([DefaultTranslationsSeeder::class, TableSeeder::class]);
    $this->table = Table::first();

    $this->book = fn (ReservationStatus $status, mixed $startAt, ?string $guest = 'Guest') => Reservation::create([
        'table_id' => $this->table->id,
        'start_at' => $startAt,
        'status' => $status,
        'guests_count' => 2,
        'source' => 'phone',
        'guest_name' => $guest,
    ]);
});

test('the scheduler expires pending reservations more than an hour late', function () {
    $late = ($this->book)(ReservationStatus::Pending, now()->subMinutes(61));
    $recent = ($this->book)(ReservationStatus::Pending, now()->subMinutes(30));
    $seated = ($this->book)(ReservationStatus::Active, now()->subHours(3));

    $this->artisan('reservations:expire')->assertSuccessful();

    expect($late->fresh()->status)->toBe(ReservationStatus::Expired)
        ->and($recent->fresh()->status)->toBe(ReservationStatus::Pending)
        ->and($seated->fresh()->status)->toBe(ReservationStatus::Active);
});

test('a late reservation cannot start an order before the scheduler expires it', function () {
    $staff = User::factory()->create();
    $staff->forceFill(['role' => 'staff', 'email_verified_at' => now()])->save();
    $late = ($this->book)(ReservationStatus::Pending, now()->subMinutes(90));

    $this->actingAs($staff)
        ->post(route('staff.orders.from-reservation', ['locale' => 'en', 'reservation' => $late]))
        ->assertSessionHas('error');

    expect($late->fresh()->order)->toBeNull();
});

test('staff Telegram messages use the staff language and escape guest input', function () {
    config(['services.telegram' => ['staff_bot_token' => 'test-token', 'staff_chat_id' => '42', 'orders_chat_id' => '43', 'locale' => 'en']]);
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

    TelegramNotifier::notifyNewReservation(($this->book)(ReservationStatus::Pending, now()->addDay(), '<b>Bob</b>'));

    Http::assertSent(fn (Request $request) => $request['chat_id'] === '42'
        && str_contains($request['text'], 'NEW RESERVATION')
        && str_contains($request['text'], '&lt;b&gt;Bob&lt;/b&gt;'));
});

test('nothing is sent to Telegram until the bot is configured', function () {
    config(['services.telegram' => ['staff_bot_token' => null, 'staff_chat_id' => null, 'orders_chat_id' => null, 'locale' => null]]);
    Http::fake();

    TelegramNotifier::notifyNewReservation(($this->book)(ReservationStatus::Pending, now()->addDay()));

    Http::assertNothingSent();
});
