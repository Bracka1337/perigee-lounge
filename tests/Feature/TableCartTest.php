<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\DefaultTranslationsSeeder;
use Database\Seeders\MenuSeeder;
use Database\Seeders\TableSeeder;

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

    expect(Order::count())->toBe(0);
    $this->get(route('client.table.cart.show', ['locale' => 'en', 'code' => 'z4']))
        ->assertDontSee($this->item->t('name'));
});
