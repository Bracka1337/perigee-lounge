<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\User;
use Database\Seeders\DefaultTranslationsSeeder;
use Database\Seeders\MenuSeeder;
use Database\Seeders\TableSeeder;

beforeEach(function () {
    $this->seed([DefaultTranslationsSeeder::class, MenuSeeder::class]);
});

test('menu shows items translated for the locale', function (string $locale, string $category, string $description) {
    $this->get("/{$locale}/menu")
        ->assertOk()
        ->assertSee($category)
        ->assertSee($description)
        ->assertSee('18.00 €');
})->with([
    ['en', 'Hookah', 'Mint and bergamot.'],
    ['lv', 'Ūdenspīpes', 'Piparmētra un bergamote.'],
    ['ru', 'Кальяны', 'Мята и бергамот.'],
]);

test('category names are not double escaped in item data', function () {
    $this->get('/en/menu')
        ->assertOk()
        ->assertSee('data-category="Tea &amp; coffee"', false)
        ->assertDontSee('&amp;amp;', false);
});

test('qr table menu lets signed in guests add items to the cart', function () {
    $this->seed(TableSeeder::class);

    $this->actingAs(User::factory()->create())
        ->get('/en/menu/table-z4')
        ->assertOk()
        ->assertSee('Z4')
        ->assertSee('name="menu_item_id"', false)
        ->assertSee('data-menu-item', false);
});

test('regular menu has no cart controls', function () {
    $this->get('/en/menu')
        ->assertOk()
        ->assertDontSee('name="menu_item_id"', false);
});

test('unavailable items cannot be opened', function () {
    MenuItem::query()->update(['is_available' => false]);

    $this->get('/en/menu')
        ->assertOk()
        ->assertDontSee('data-menu-item', false);
});

test('unavailable categories disable their items', function () {
    MenuCategory::query()->update(['is_available' => false]);

    $this->get('/en/menu')
        ->assertOk()
        ->assertDontSee('data-menu-item', false);
});
