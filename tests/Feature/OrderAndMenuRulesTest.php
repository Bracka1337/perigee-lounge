<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PromoCode;
use App\Models\Reservation;
use App\Models\Table;
use App\Models\User;
use Database\Seeders\DefaultTranslationsSeeder;
use Database\Seeders\MenuSeeder;
use Database\Seeders\TableSeeder;

function userWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => $role, 'email_verified_at' => now()])->save();

    return $user;
}

function openOrder(Table $table): Order
{
    $table->update(['is_occupied' => true]);

    return Order::create([
        'table_id' => $table->id,
        'status' => Order::STATUS_OPEN,
        'source' => 'staff',
        'subtotal' => 0,
        'discount_total' => 0,
        'total' => 0,
    ]);
}

function addItem(Order $order, MenuItem $item, float $price): void
{
    OrderItem::create([
        'order_id' => $order->id,
        'menu_item_id' => $item->id,
        'quantity' => 1,
        'unit_price' => $price,
        'total_price' => $price,
    ]);
}

function reservationOn(Table $table, string $status, mixed $startAt): Reservation
{
    return Reservation::create([
        'table_id' => $table->id,
        'start_at' => $startAt,
        'status' => $status,
        'guests_count' => 2,
        'source' => 'phone',
        'guest_name' => 'Guest',
    ]);
}

beforeEach(function () {
    $this->seed([DefaultTranslationsSeeder::class, MenuSeeder::class, TableSeeder::class]);
    $this->staff = userWithRole('staff');
    $this->table = Table::first();
    $this->item = MenuItem::first();
});

test('completing an order frees the table when the only other reservation is in the future', function () {
    $order = openOrder($this->table);
    reservationOn($this->table, Reservation::STATUS_PENDING, now()->addWeek());

    $this->actingAs($this->staff)
        ->post(route('staff.orders.complete', ['locale' => 'en', 'order' => $order]))
        ->assertRedirect();

    expect($this->table->fresh()->is_occupied)->toBeFalse();
});

test('completing an order keeps the table occupied while a guest is seated there', function () {
    $order = openOrder($this->table);
    reservationOn($this->table, Reservation::STATUS_ACTIVE, now());

    $this->actingAs($this->staff)
        ->post(route('staff.orders.complete', ['locale' => 'en', 'order' => $order]))
        ->assertRedirect();

    expect($this->table->fresh()->is_occupied)->toBeTrue();
});

test('a menu item or category with ordered items cannot be deleted', function () {
    $order = openOrder($this->table);
    addItem($order, $this->item, 5);

    $this->actingAs($this->staff)
        ->delete(route('staff.menu-items.destroy', ['locale' => 'en', 'menu_item' => $this->item]))
        ->assertSessionHas('error');

    expect(MenuItem::find($this->item->id))->not->toBeNull()
        ->and(OrderItem::where('order_id', $order->id)->count())->toBe(1);

    $this->actingAs($this->staff)
        ->delete(route('staff.menu-categories.destroy', ['locale' => 'en', 'menu_category' => $this->item->menu_category_id]))
        ->assertSessionHas('error');

    expect(MenuCategory::find($this->item->menu_category_id))->not->toBeNull();
});

test('the staff menu page shows why a delete was blocked', function () {
    addItem(openOrder($this->table), $this->item, 5);

    $this->actingAs($this->staff)
        ->followingRedirects()
        ->delete(route('staff.menu-items.destroy', ['locale' => 'en', 'menu_item' => $this->item]))
        ->assertOk()
        ->assertSee('This item appears in orders');
});

test('a menu item that was never ordered can be deleted', function () {
    $this->actingAs($this->staff)
        ->delete(route('staff.menu-items.destroy', ['locale' => 'en', 'menu_item' => $this->item]))
        ->assertSessionHas('success');

    expect(MenuItem::find($this->item->id))->toBeNull();
});

test('the staff menu page falls back when a translation is missing', function () {
    $this->item->update(['name' => ['ru' => 'Только русский'], 'description' => ['ru' => 'Описание']]);
    $this->item->category->update(['name' => ['ru' => 'Категория'], 'description' => ['ru' => 'Текст']]);

    $this->actingAs($this->staff)
        ->get(route('staff.menu-categories.index', ['locale' => 'en']))
        ->assertOk()
        ->assertSee('Только русский')
        ->assertSee('Категория');
});

test('percent promo codes cannot be above 100, fixed ones can', function () {
    $this->actingAs($this->staff)
        ->post(route('staff.promo-codes.store', ['locale' => 'en']), ['discount_type' => 'percent', 'discount_value' => 500, 'scope' => 'all'])
        ->assertSessionHasErrors('discount_value');

    $this->actingAs($this->staff)
        ->post(route('staff.promo-codes.store', ['locale' => 'en']), ['discount_type' => 'fixed', 'discount_value' => 500, 'scope' => 'all'])
        ->assertSessionHasNoErrors();
});

test('an old promo code above 100 percent never discounts more than the order', function () {
    $promo = new PromoCode(['discount_type' => 'percent', 'discount_value' => 500, 'scope' => 'all']);
    $order = openOrder($this->table);
    addItem($order, $this->item, 18);

    expect((float) $promo->calculateDiscountForOrder($order->fresh('items')))->toBe(18.0);
});

test('bad date filters fall back to today instead of failing', function (string $route, string $role, array|string $date) {
    $this->actingAs(userWithRole($role))
        ->get(route($route, ['locale' => 'en', 'date' => $date]))
        ->assertOk();
})->with([
    ['staff.orders.index', 'staff', 'garbage'],
    ['staff.orders.index', 'staff', ['x']],
    ['staff.reservations.index', 'staff', '2026-13-45'],
    ['client.reservations.index', 'client', '99999-99-99'],
]);

test('reservations reject a date with a time in it', function () {
    $this->actingAs($this->staff)
        ->post(route('staff.reservations.store', ['locale' => 'en']), [
            'table_id' => $this->table->id,
            'date' => '2030-11-05 10:00',
            'time' => '19:00',
            'mode' => 'phone',
            'guests_count' => 2,
        ])
        ->assertSessionHasErrors('date');
});

function clientWithPoints(float $points, string $code): User
{
    $client = userWithRole('client');
    $client->forceFill(['loyalty_balance' => $points, 'loyalty_code' => $code])->save();

    return $client;
}

function spendLoyalty(User $staff, Order $order, User $client, float $amount): void
{
    $order->update(['user_id' => $client->id]);
    $order->refresh()->recalculateTotals();
    $order->save();

    test()->actingAs($staff)
        ->post(route('staff.orders.apply-loyalty', ['locale' => 'en', 'order' => $order]), ['amount' => $amount])
        ->assertSessionHas('success');
}

test('an order cannot be started from a reservation that is over', function (string $status) {
    $reservation = reservationOn($this->table, $status, now()->subHour());

    $this->actingAs($this->staff)
        ->post(route('staff.orders.from-reservation', ['locale' => 'en', 'reservation' => $reservation]))
        ->assertSessionHas('error');

    expect(Order::count())->toBe(0)
        ->and($this->table->fresh()->is_occupied)->toBeFalse()
        ->and($reservation->fresh()->status)->toBe($status);
})->with([Reservation::STATUS_CANCELLED, Reservation::STATUS_COMPLETED, Reservation::STATUS_EXPIRED]);

test('an order can still be started from a pending reservation', function () {
    $reservation = reservationOn($this->table, Reservation::STATUS_PENDING, now()->subMinute());

    $this->actingAs($this->staff)
        ->post(route('staff.orders.from-reservation', ['locale' => 'en', 'reservation' => $reservation]))
        ->assertSessionHas('success');

    expect(Order::where('reservation_id', $reservation->id)->count())->toBe(1);
});

test('removing every item returns the loyalty points spent on the order', function () {
    $client = clientWithPoints(50, 'REFUND-ALL');
    $order = openOrder($this->table);
    addItem($order, $this->item, 20);
    spendLoyalty($this->staff, $order, $client, 5);
    spendLoyalty($this->staff, $order, $client, 10);

    $this->actingAs($this->staff)
        ->delete(route('staff.orders.items.destroy', ['locale' => 'en', 'order' => $order, 'item' => $order->items()->first()]))
        ->assertSessionHas('success');

    $order->refresh();
    expect((float) $client->fresh()->loyalty_balance)->toBe(50.0)
        ->and((float) $order->discount_total)->toBe(0.0)
        ->and((float) $order->total)->toBe(0.0)
        ->and($order->user_id)->toBe($client->id);
});

test('lowering a quantity returns only the points that no longer fit the order', function () {
    $client = clientWithPoints(50, 'REFUND-PART');
    $order = openOrder($this->table);
    OrderItem::create(['order_id' => $order->id, 'menu_item_id' => $this->item->id, 'quantity' => 3, 'unit_price' => 10, 'total_price' => 30]);
    spendLoyalty($this->staff, $order, $client, 25);

    $this->actingAs($this->staff)
        ->patch(route('staff.orders.items.update', ['locale' => 'en', 'order' => $order, 'item' => $order->items()->first()]), ['quantity' => 2])
        ->assertSessionHas('success');

    $order->refresh();
    expect((float) $client->fresh()->loyalty_balance)->toBe(30.0)
        ->and((float) $order->subtotal)->toBe(20.0)
        ->and((float) $order->discount_total)->toBe(20.0)
        ->and((float) $order->total)->toBe(0.0);
});

test('removing an item keeps the points while they still fit the order', function () {
    $client = clientWithPoints(50, 'REFUND-NONE');
    $order = openOrder($this->table);
    addItem($order, $this->item, 20);
    addItem($order, MenuItem::where('id', '!=', $this->item->id)->first(), 18);
    spendLoyalty($this->staff, $order, $client, 10);

    $this->actingAs($this->staff)
        ->delete(route('staff.orders.items.destroy', ['locale' => 'en', 'order' => $order, 'item' => $order->items()->latest('id')->first()]))
        ->assertSessionHas('success');

    $order->refresh();
    expect((float) $client->fresh()->loyalty_balance)->toBe(40.0)
        ->and((float) $order->total)->toBe(10.0);
});

test('applying a promo returns the points it pushes past the order total', function () {
    $client = clientWithPoints(50, 'REFUND-PROMO');
    $order = openOrder($this->table);
    addItem($order, $this->item, 20);
    spendLoyalty($this->staff, $order, $client, 15);

    $promo = new PromoCode;
    $promo->forceFill([
        'public_code' => 'HALF', 'qr_code' => 'HALF-QR', 'discount_type' => 'percent', 'discount_value' => 50,
        'scope' => 'all', 'is_active' => true, 'used_count' => 0,
    ])->save();

    $this->actingAs($this->staff)
        ->post(route('staff.orders.apply-promo', ['locale' => 'en', 'order' => $order]), ['code' => 'HALF'])
        ->assertSessionHas('success');

    $order->refresh();
    expect((float) $client->fresh()->loyalty_balance)->toBe(40.0)
        ->and((float) $order->discount_total)->toBe(20.0)
        ->and((float) $order->total)->toBe(0.0);
});

test('the client cannot be changed once they spent points on the order', function () {
    $first = clientWithPoints(50, 'SWAP-FIRST');
    clientWithPoints(50, 'SWAP-SECOND');
    $order = openOrder($this->table);
    addItem($order, $this->item, 20);
    spendLoyalty($this->staff, $order, $first, 10);

    $this->actingAs($this->staff)
        ->post(route('staff.orders.attach-client', ['locale' => 'en', 'order' => $order]), ['loyalty_code' => 'SWAP-SECOND'])
        ->assertSessionHas('error');

    expect($order->fresh()->user_id)->toBe($first->id);
});

test('the client can be changed on an open order with no points spent', function () {
    $first = clientWithPoints(50, 'FREE-FIRST');
    $second = clientWithPoints(50, 'FREE-SECOND');
    $order = openOrder($this->table);
    $order->update(['user_id' => $first->id]);

    $this->actingAs($this->staff)
        ->post(route('staff.orders.attach-client', ['locale' => 'en', 'order' => $order]), ['loyalty_code' => 'FREE-SECOND'])
        ->assertSessionHas('success');

    expect($order->fresh()->user_id)->toBe($second->id);
});

test('the client of a finished order cannot be changed', function () {
    $first = clientWithPoints(0, 'DONE-FIRST');
    clientWithPoints(0, 'DONE-SECOND');
    $order = openOrder($this->table);
    $order->update(['user_id' => $first->id, 'status' => Order::STATUS_COMPLETED]);

    $this->actingAs($this->staff)
        ->post(route('staff.orders.attach-client', ['locale' => 'en', 'order' => $order]), ['loyalty_code' => 'DONE-SECOND'])
        ->assertSessionHas('error');

    expect($order->fresh()->user_id)->toBe($first->id);
});

test('marking a guest as arrived opens an order for their table', function () {
    $reservation = reservationOn($this->table, Reservation::STATUS_PENDING, now()->subMinutes(10));

    $response = $this->actingAs($this->staff)
        ->patch(route('staff.reservations.arrive', ['locale' => 'en', 'reservation' => $reservation]));

    $order = Order::where('reservation_id', $reservation->id)->firstOrFail();
    $response->assertRedirect(route('staff.orders.show', ['locale' => 'en', 'order' => $order]));

    expect($order->status)->toBe(Order::STATUS_OPEN)
        ->and($order->source)->toBe('phone')
        ->and($reservation->fresh()->status)->toBe(Reservation::STATUS_ACTIVE)
        ->and($this->table->fresh()->is_occupied)->toBeTrue();
});

test('marking a guest as arrived links the walk-in order already open on the table', function () {
    $order = openOrder($this->table);
    $reservation = reservationOn($this->table, Reservation::STATUS_PENDING, now()->subMinutes(10));

    $this->actingAs($this->staff)
        ->patch(route('staff.reservations.arrive', ['locale' => 'en', 'reservation' => $reservation]))
        ->assertRedirect(route('staff.orders.show', ['locale' => 'en', 'order' => $order]));

    expect($order->fresh()->reservation_id)->toBe($reservation->id)
        ->and(Order::count())->toBe(1);
});

test('a guest cannot be marked as arrived before the reservation starts', function () {
    $reservation = reservationOn($this->table, Reservation::STATUS_PENDING, now()->addHour());

    $this->actingAs($this->staff)
        ->patch(route('staff.reservations.arrive', ['locale' => 'en', 'reservation' => $reservation]))
        ->assertSessionHas('error');

    expect($reservation->fresh()->status)->toBe(Reservation::STATUS_PENDING)
        ->and(Order::count())->toBe(0);
});

test('completing a reservation completes its order, awards points and frees the table', function () {
    $client = userWithRole('client');
    $reservation = reservationOn($this->table, Reservation::STATUS_ACTIVE, now()->subHour());
    $reservation->update(['user_id' => $client->id]);
    $this->table->update(['is_occupied' => true]);

    $order = Order::create([
        'reservation_id' => $reservation->id,
        'table_id' => $this->table->id,
        'user_id' => $client->id,
        'status' => Order::STATUS_OPEN,
        'source' => 'phone',
    ]);
    addItem($order, $this->item, 200);

    $this->actingAs($this->staff)
        ->patch(route('staff.reservations.complete', ['locale' => 'en', 'reservation' => $reservation]))
        ->assertSessionHas('success');

    expect($reservation->fresh()->status)->toBe(Reservation::STATUS_COMPLETED)
        ->and($order->fresh()->status)->toBe(Order::STATUS_COMPLETED)
        ->and((float) $order->fresh()->total)->toBe(200.0)
        ->and((float) $client->fresh()->loyalty_balance)->toBe(2.0)
        ->and($this->table->fresh()->is_occupied)->toBeFalse();
});

test('completing an order awards the client one percent in points', function () {
    $client = userWithRole('client');
    $order = openOrder($this->table);
    $order->update(['user_id' => $client->id]);
    addItem($order, $this->item, 150);

    $this->actingAs($this->staff)
        ->post(route('staff.orders.complete', ['locale' => 'en', 'order' => $order]))
        ->assertRedirect(route('staff.orders.index', ['locale' => 'en']));

    expect($order->fresh()->status)->toBe(Order::STATUS_COMPLETED)
        ->and((float) $client->fresh()->loyalty_balance)->toBe(1.5);
});
