<?php

use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Table;
use App\Models\User;
use Database\Seeders\DefaultTranslationsSeeder;
use Database\Seeders\MenuSeeder;
use Database\Seeders\TableSeeder;

function accountWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => $role, 'email_verified_at' => now()])->save();

    return $user;
}

beforeEach(function () {
    $this->seed([DefaultTranslationsSeeder::class, MenuSeeder::class, TableSeeder::class]);
    $this->table = Table::first();
});

test('guests are sent to login from write routes', function (string $route) {
    $this->post(route($route, ['locale' => 'en']))
        ->assertRedirectContains('/login');
})->with([
    'client.reservations.store',
    'client.promo-codes.store',
    'staff.orders.store',
    'staff.promo-codes.store',
    'admin.users.store',
]);

test('the wrong role gets 403', function (string $role, string $route) {
    $this->actingAs(accountWithRole($role))
        ->post(route($route, ['locale' => 'en']))
        ->assertForbidden();
})->with([
    ['client', 'staff.orders.store'],
    ['client', 'staff.promo-codes.store'],
    ['client', 'staff.menu.sort'],
    ['client', 'admin.users.store'],
    ['staff', 'admin.users.store'],
]);

test('a client cannot open another client\'s order', function () {
    $order = Order::create([
        'table_id' => $this->table->id,
        'user_id' => accountWithRole('client')->id,
        'status' => Order::STATUS_OPEN,
        'source' => 'staff',
        'subtotal' => 0,
        'discount_total' => 0,
        'total' => 0,
    ]);

    $this->actingAs(accountWithRole('client'))
        ->get(route('client.orders.show', ['locale' => 'en', 'order' => $order]))
        ->assertNotFound();

    $this->actingAs($order->user)
        ->get(route('client.orders.show', ['locale' => 'en', 'order' => $order]))
        ->assertOk();
});

test('a client cannot cancel another client\'s reservation', function () {
    $reservation = Reservation::create([
        'table_id' => $this->table->id,
        'user_id' => accountWithRole('client')->id,
        'start_at' => now()->addWeek(),
        'status' => Reservation::STATUS_PENDING,
        'guests_count' => 2,
        'source' => 'client',
    ]);

    $this->actingAs(accountWithRole('client'))
        ->post(route('client.reservations.cancel', ['locale' => 'en', 'reservation' => $reservation]))
        ->assertForbidden();

    expect($reservation->fresh()->status)->toBe(Reservation::STATUS_PENDING);
});

test('reservations validate their input', function (array $override, string $field) {
    $valid = [
        'table_id' => $this->table->id,
        'date' => now()->addWeek()->toDateString(),
        'time' => '19:00',
        'guests_count' => 2,
    ];

    $this->actingAs(accountWithRole('client'))
        ->post(route('client.reservations.store', ['locale' => 'en']), array_merge($valid, $override))
        ->assertSessionHasErrors($field);

    expect(Reservation::count())->toBe(0);
})->with([
    [['table_id' => 999999], 'table_id'],
    [['date' => 'garbage'], 'date'],
    [['time' => '25:99'], 'time'],
    [['guests_count' => 0], 'guests_count'],
    [['guests_count' => 21], 'guests_count'],
]);

test('staff can sort menu categories through the JSON endpoint', function () {
    $ids = MenuCategory::orderBy('sort_order')->pluck('id')->all();
    $reversed = array_reverse($ids);

    $this->actingAs(accountWithRole('staff'))
        ->postJson(route('staff.menu.sort', ['locale' => 'en']), [
            'categories' => array_map(fn ($id) => ['id' => $id, 'items' => []], $reversed),
        ])
        ->assertOk()
        ->assertJson(['status' => 'ok']);

    expect(MenuCategory::orderBy('sort_order')->pluck('id')->all())->toBe($reversed);
});
