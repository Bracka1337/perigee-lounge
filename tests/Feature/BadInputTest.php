<?php

use App\Enums\OrderStatus;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\User;
use Database\Seeders\DefaultTranslationsSeeder;
use Database\Seeders\MenuSeeder;
use Database\Seeders\TableSeeder;

// Malformed input must give a validation error or a 404, never a 500.

beforeEach(function () {
    $this->seed([DefaultTranslationsSeeder::class, MenuSeeder::class, TableSeeder::class]);

    $this->as = function (string $role): User {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role, 'email_verified_at' => now()])->save();

        return $user;
    };
});

test('staff can add at most 99 of an item to an order', function () {
    $order = Order::create(['table_id' => Table::first()->id, 'status' => OrderStatus::Open, 'source' => 'staff']);
    $item = MenuItem::first();
    $staff = ($this->as)('staff');

    $this->actingAs($staff)
        ->post(route('staff.orders.items.store', ['locale' => 'en', 'order' => $order]), ['menu_item_id' => $item->id, 'quantity' => 100])
        ->assertSessionHasErrors('quantity');

    $this->actingAs($staff)
        ->post(route('staff.orders.items.store', ['locale' => 'en', 'order' => $order]), ['menu_item_id' => $item->id, 'quantity' => 60]);
    $this->actingAs($staff)
        ->post(route('staff.orders.items.store', ['locale' => 'en', 'order' => $order]), ['menu_item_id' => $item->id, 'quantity' => 60])
        ->assertSessionHasErrors('quantity');

    expect((int) OrderItem::where('order_id', $order->id)->sum('quantity'))->toBe(60);
});

test('staff cannot add an unavailable menu item to an order', function () {
    $order = Order::create(['table_id' => Table::first()->id, 'status' => OrderStatus::Open, 'source' => 'staff']);
    $item = MenuItem::first();
    $item->update(['is_available' => false]);

    $this->actingAs(($this->as)('staff'))
        ->post(route('staff.orders.items.store', ['locale' => 'en', 'order' => $order]), ['menu_item_id' => $item->id])
        ->assertSessionHas('error');

    expect($order->items()->count())->toBe(0);
});

test('the menu sort endpoint rejects malformed JSON', function (array $payload) {
    $this->actingAs(($this->as)('staff'))
        ->postJson(route('staff.menu.sort', ['locale' => 'en']), $payload)
        ->assertUnprocessable();
})->with([
    'categories not a list' => [['categories' => 'x']],
    'items not a list' => [['categories' => [['id' => 1, 'items' => 'x']]]],
    'id is a list' => [['categories' => [['id' => [1]]]]],
    'item is an object' => [['categories' => [['id' => 1, 'items' => [['a' => 1]]]]]],
]);

test('the table layout rejects bad values and missing keys', function (array $row) {
    $this->actingAs(($this->as)('admin'))
        ->post(route('admin.tables.layout.update', ['locale' => 'en']), ['layout_payload' => json_encode([$row])])
        ->assertSessionHasErrors();
})->with([
    'text position' => [['id' => 1, 'pos_x' => 'abc', 'pos_y' => 1, 'width' => 1, 'height' => 1, 'rotation' => 0]],
    'missing keys' => [['id' => 1]],
]);

test('opening hours accept only weekdays 0 to 6 with known fields', function (array $hours) {
    $this->actingAs(($this->as)('admin'))
        ->post(route('admin.tables.opening-hours.update', ['locale' => 'en']), ['hours' => $hours])
        ->assertSessionHasErrors();
})->with([
    'unknown weekday' => [[99 => ['is_open' => 1, 'opens_at' => '18:00', 'closes_at' => '23:00']]],
    'unknown field' => [[1 => ['open' => 1]]],
]);

test('array query and form values give validation errors, not server errors', function (string $role, string $method, string $url, array $data) {
    $user = ($this->as)($role);

    $status = $this->actingAs($user)->call($method, $url, $data)->status();

    expect($status)->toBeIn([200, 302]);
})->with([
    'translation search' => ['admin', 'GET', '/en/admin/translations?q[]=x', []],
    'log filter' => ['admin', 'GET', '/en/admin/logs?model_id[]=1', []],
    'password update' => ['client', 'PUT', '/en/password', ['current_password' => ['x'], 'password' => ['y'], 'password_confirmation' => ['y']]],
    'order table id' => ['staff', 'POST', '/en/staff/orders', ['table_id' => [1]]],
]);

test('registration with array values gives validation errors', function () {
    $this->post(route('register', ['locale' => 'en']), [
        'name' => 'Test', 'email' => ['test@example.com'], 'birth_date' => '1990-01-01',
        'password' => 'Secret123!', 'password_confirmation' => 'Secret123!', 'terms' => '1',
    ])->assertSessionHasErrors('email');
});

test('a non-numeric translation id is a 404', function () {
    $this->actingAs(($this->as)('admin'))
        ->get('/en/admin/translations/abc/edit')
        ->assertNotFound();
});
