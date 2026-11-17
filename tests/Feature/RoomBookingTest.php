<?php

use App\Enums\ReservationStatus;
use App\Models\Floor;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Table;
use App\Models\User;
use App\Services\TableHolds;
use Database\Seeders\DefaultTranslationsSeeder;
use Database\Seeders\OpeningHoursSeeder;
use Database\Seeders\TableSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

function bookingUser(string $role): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => $role, 'email_verified_at' => now()])->save();

    return $user;
}

function vip(): Room
{
    return Room::where('name->en', 'VIP')->firstOrFail();
}

/**
 * A booking time well inside opening hours, tomorrow.
 *
 * @return array{date: string, time: string}
 */
function bookingSlot(): array
{
    return ['date' => Carbon::tomorrow()->toDateString(), 'time' => '20:00'];
}

beforeEach(function () {
    Notification::fake();
    $this->seed([DefaultTranslationsSeeder::class, OpeningHoursSeeder::class, TableSeeder::class]);
});

test('the default layout lets four rooms be booked as a whole', function () {
    expect(Room::where('is_bookable_whole', true)->pluck('name')->map(fn ($name) => $name['en'])->sort()->values()->all())
        ->toBe(['Cinema room', 'Game room', 'Karaoke room', 'VIP']);
});

test('a client can book a whole room, which holds every table in it', function () {
    $client = bookingUser('client');
    $cinema = Room::where('name->en', 'Cinema room')->firstOrFail();

    $this->actingAs($client)
        ->post(route('client.reservations.store'), [...bookingSlot(), 'room_id' => $cinema->id, 'guests_count' => 11])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success');

    $reservation = Reservation::firstOrFail();
    expect($reservation->room_id)->toBe($cinema->id)
        ->and($reservation->table->room_id)->toBe($cinema->id)
        ->and($reservation->status)->toBe(ReservationStatus::Pending);

    // Every table of the cinema is now taken that day, and none elsewhere.
    $held = app(TableHolds::class)->byTable(app(TableHolds::class)->on(bookingSlot()['date']))->keys()->sort()->values()->all();
    expect($held)->toBe($cinema->tables()->pluck('id')->sort()->values()->all());

    // So a table in the cinema cannot be booked separately by someone else.
    $this->actingAs(bookingUser('client'))
        ->post(route('client.reservations.store'), [...bookingSlot(), 'table_id' => $cinema->tables()->value('id'), 'guests_count' => 2])
        ->assertSessionHas('error', __('client-reservations-error-overlap'));
    expect(Reservation::count())->toBe(1);
});

test('a room cannot be booked when one of its tables is taken', function () {
    $vip = vip();
    $table = $vip->tables()->firstOrFail();
    Reservation::create(['table_id' => $table->id, 'start_at' => Carbon::tomorrow()->setTime(19, 0), 'status' => ReservationStatus::Pending, 'guests_count' => 4, 'source' => 'phone']);

    $this->actingAs(bookingUser('client'))->from(route('client.reservations.index'))
        ->post(route('client.reservations.store'), [...bookingSlot(), 'room_id' => $vip->id, 'guests_count' => 6])
        ->assertSessionHas('error', __('room-booking-error-taken'));

    expect(Reservation::whereNotNull('room_id')->exists())->toBeFalse();
});

test('a room with an open order cannot be booked', function () {
    $game = Room::where('name->en', 'Game room')->firstOrFail();
    Order::create(['table_id' => $game->tables()->value('id'), 'status' => 'open', 'started_at' => now()]);

    $this->actingAs(bookingUser('client'))->from(route('client.reservations.index'))
        ->post(route('client.reservations.store'), [...bookingSlot(), 'room_id' => $game->id, 'guests_count' => 4])
        ->assertSessionHas('error', __('room-booking-error-taken'));
});

test('a whole-room booking checks the room and the guests', function (string $case) {
    $room = match ($case) {
        'not bookable' => Room::where('name->en', 'Main hall')->firstOrFail(),
        default => vip(),
    };
    $guests = $case === 'too many guests' ? 21 : 4;

    $this->actingAs(bookingUser('client'))->from(route('client.reservations.index'))
        ->post(route('client.reservations.store'), [...bookingSlot(), 'room_id' => $room->id, 'guests_count' => $guests])
        ->assertSessionHas('error', __($case === 'not bookable' ? 'room-booking-error-not-bookable' : 'room-booking-error-too-many-guests'));

    expect(Reservation::count())->toBe(0);
})->with(['not bookable', 'too many guests']);

test('a booking names either a table or a room, not both', function () {
    $vip = vip();

    $this->actingAs(bookingUser('client'))
        ->post(route('client.reservations.store'), [...bookingSlot(), 'room_id' => $vip->id, 'table_id' => $vip->tables()->value('id'), 'guests_count' => 4])
        ->assertSessionHasErrors('table_id');

    $this->actingAs(bookingUser('client'))
        ->post(route('client.reservations.store'), [...bookingSlot(), 'guests_count' => 4])
        ->assertSessionHasErrors(['table_id', 'room_id']);
});

test('staff can book a whole room for a phone guest', function () {
    $karaoke = Room::where('name->en', 'Karaoke room')->firstOrFail();

    $this->actingAs(bookingUser('staff'))
        ->post(route('staff.reservations.store'), [...bookingSlot(), 'room_id' => $karaoke->id, 'mode' => 'phone', 'guest_name' => 'Anna', 'guest_phone' => '+37120000000', 'guests_count' => 10])
        ->assertSessionHasNoErrors();

    $reservation = Reservation::firstOrFail();
    expect($reservation->room_id)->toBe($karaoke->id)
        ->and($reservation->guest_name)->toBe('Anna')
        ->and($reservation->placeName())->toBe('Karaoke room');
});

test('the booking pages offer bookable rooms and show what is taken', function () {
    $client = bookingUser('client');
    $vip = vip();

    $this->actingAs($client)->get(route('client.reservations.index', ['date' => bookingSlot()['date']]))
        ->assertOk()
        ->assertSee('data-room-id="'.$vip->id.'"', false)
        ->assertSee('data-taken="0"', false)
        ->assertViewHas('roomBooking', fn (array $rooms) => $rooms[$vip->id] === ['seats' => 20, 'taken' => false, 'booked' => false]);

    Reservation::create(['table_id' => $vip->tables()->value('id'), 'room_id' => $vip->id, 'user_id' => $client->id, 'start_at' => Carbon::parse(bookingSlot()['date'].' 20:00'), 'status' => ReservationStatus::Pending, 'guests_count' => 8, 'source' => 'client']);

    $this->actingAs($client)->get(route('client.reservations.index', ['date' => bookingSlot()['date']]))
        ->assertViewHas('roomBooking', fn (array $rooms) => $rooms[$vip->id]['booked'] && $rooms[$vip->id]['taken'])
        ->assertSee('Whole room VIP');

    $this->actingAs(bookingUser('staff'))->get(route('staff.reservations.index', ['date' => bookingSlot()['date']]))
        ->assertOk()
        ->assertSee('Whole room VIP');
});

test('a room with whole-room bookings cannot be removed from the plan', function () {
    $vip = vip();
    $floor = Floor::findOrFail($vip->floor_id);
    Reservation::create(['table_id' => $vip->tables()->value('id'), 'room_id' => $vip->id, 'start_at' => Carbon::tomorrow()->setTime(20, 0), 'status' => ReservationStatus::Completed, 'guests_count' => 8, 'source' => 'phone']);

    // Move the VIP table into the bar, so only the booking keeps the room.
    $bar = Room::where('floor_id', $floor->id)->where('name->en', 'Bar')->firstOrFail();
    $rooms = $floor->rooms()->get()->reject(fn (Room $room) => $room->id === $vip->id);
    $tables = Table::whereIn('room_id', $floor->rooms()->pluck('id'))->get()->map(fn (Table $table) => [
        'id' => $table->id, 'name' => $table->name, 'capacity' => $table->capacity,
        'x' => $table->room_id === $vip->id ? $bar->x + 1 : $table->pos_x, 'y' => $table->room_id === $vip->id ? $bar->y + 3 : $table->pos_y,
        'width' => $table->room_id === $vip->id ? 100 : $table->width, 'height' => $table->room_id === $vip->id ? 100 : $table->height,
        'rotation' => 0, 'is_active' => true,
    ]);
    $plan = ['rooms' => $rooms->map(fn (Room $room) => [
        'id' => $room->id, 'name' => $room->name, 'kind' => $room->kind->value, 'color' => $room->color->value,
        'x' => $room->x, 'y' => $room->y, 'width' => $room->width, 'height' => $room->height,
    ])->values()->all(), 'tables' => $tables->values()->all()];

    $this->actingAs(bookingUser('admin'))->from(route('admin.floor-plan'))
        ->put(route('admin.floors.plan.update', ['floor' => $floor]), ['plan_payload' => json_encode($plan)])
        ->assertSessionHas('error', __('admin-rooms-delete-has-bookings'));

    expect(Room::find($vip->id))->not->toBeNull();
});
