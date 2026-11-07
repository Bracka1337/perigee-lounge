<?php

use App\Enums\OrderStatus;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\User;
use Database\Seeders\DefaultTranslationsSeeder;
use Database\Seeders\MenuSeeder;
use Database\Seeders\TableSeeder;

function cartUrl(): string
{
    return route('client.table.cart.show', ['locale' => 'en', 'code' => 'z4']);
}

beforeEach(function () {
    $this->seed([DefaultTranslationsSeeder::class, MenuSeeder::class, TableSeeder::class]);

    $this->item = MenuItem::first();
    $this->addUrl = route('client.table.cart.add', ['locale' => 'en', 'code' => 'z4']);
    $this->actingAs(User::factory()->create());
});

test('available items can be added to the cart', function () {
    $this->post($this->addUrl, ['menu_item_id' => $this->item->id])
        ->assertRedirect()
        ->assertSessionHasNoErrors()
        ->assertSessionMissing('error');

    $this->get(route('client.table.cart.show', ['locale' => 'en', 'code' => 'z4']))
        ->assertOk()
        ->assertSee($this->item->t('name'));
});

test('unavailable items cannot be added to the cart', function () {
    $this->item->update(['is_available' => false]);

    $this->post($this->addUrl, ['menu_item_id' => $this->item->id])
        ->assertSessionHas('error');

    $this->get(route('client.table.cart.show', ['locale' => 'en', 'code' => 'z4']))
        ->assertDontSee($this->item->t('name'));
});

test('items in unavailable categories cannot be added to the cart', function () {
    MenuCategory::whereKey($this->item->menu_category_id)->update(['is_available' => false]);

    $this->post($this->addUrl, ['menu_item_id' => $this->item->id])
        ->assertSessionHas('error');
});

test('items that become unavailable are removed from the cart at confirmation', function () {
    $this->post($this->addUrl, ['menu_item_id' => $this->item->id]);
    $this->item->update(['is_available' => false]);

    $this->post(route('client.table.cart.confirm', ['locale' => 'en', 'code' => 'z4']))
        ->assertRedirect(route('client.table.cart.show', ['locale' => 'en', 'code' => 'z4']))
        ->assertSessionHas('error');

    $this->get(route('client.table.cart.show', ['locale' => 'en', 'code' => 'z4']))
        ->assertDontSee($this->item->t('name'));
});

test('confirming a cart adds its items to the open order', function () {
    $order = Order::create([
        'table_id' => Table::where('name', 'Z4')->value('id'),
        'status' => OrderStatus::Open,
    ]);

    $this->post($this->addUrl, ['menu_item_id' => $this->item->id, 'quantity' => 2]);
    $this->post(route('client.table.cart.confirm', ['locale' => 'en', 'code' => 'z4']));

    $orderItem = OrderItem::where('order_id', $order->id)->sole();

    expect($orderItem->menu_item_id)->toBe($this->item->id)
        ->and((int) $orderItem->quantity)->toBe(2);
});

test('items deleted from the menu are dropped from the cart page', function () {
    $this->post($this->addUrl, ['menu_item_id' => $this->item->id]);
    $name = $this->item->t('name');
    $this->item->delete();

    $this->get(cartUrl())
        ->assertOk()
        ->assertDontSee($name);
});

test('updating a quantity cannot add new items to the cart', function () {
    $other = MenuItem::whereKeyNot($this->item->id)->first();

    $this->post(route('client.table.cart.update', ['locale' => 'en', 'code' => 'z4', 'itemId' => $other->id]), ['qty' => 2])
        ->assertRedirect(cartUrl());

    $this->get(cartUrl())->assertDontSee($other->t('name'));
});

test('updating a quantity changes items already in the cart', function () {
    $this->post($this->addUrl, ['menu_item_id' => $this->item->id]);

    $this->post(route('client.table.cart.update', ['locale' => 'en', 'code' => 'z4', 'itemId' => $this->item->id]), ['qty' => 3]);

    $this->get(cartUrl())->assertSee(number_format($this->item->price * 3, 2));
});

test('non-numeric item ids are rejected', function (mixed $id) {
    $this->post($this->addUrl, ['menu_item_id' => $id])
        ->assertSessionHasErrors('menu_item_id');
})->with([
    'array' => [['x' => 1]],
    'text' => ['abc'],
]);

test('cart page has balanced markup', function (bool $withItem) {
    if ($withItem) {
        $this->post($this->addUrl, ['menu_item_id' => $this->item->id]);
    }

    $html = $this->get(cartUrl())->assertOk()->getContent();

    expect(substr_count($html, '<div'))->toBe(substr_count($html, '</div>'));
})->with(['empty' => false, 'with an item' => true]);

test('invalid item ids in cart urls are rejected without a server error', function (string $id) {
    $this->post("/en/menu/table-z4/cart/{$id}", ['qty' => 1])->assertMethodNotAllowed();
    $this->delete("/en/menu/table-z4/cart/{$id}")->assertMethodNotAllowed();
})->with(['text' => 'abc', 'too long' => '99999999999999999999']);

test('verified guests see a confirmation after sending an order', function () {
    Order::create([
        'table_id' => Table::where('name', 'Z4')->value('id'),
        'status' => OrderStatus::Open,
    ]);

    $this->post($this->addUrl, ['menu_item_id' => $this->item->id]);

    $this->followingRedirects()
        ->post(route('client.table.cart.confirm', ['locale' => 'en', 'code' => 'z4']))
        ->assertOk()
        ->assertSee('Your order was sent to the waiter');
});
