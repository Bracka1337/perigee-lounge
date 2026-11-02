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
