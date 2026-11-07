<?php

use App\Enums\OrderStatus;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Table;
use App\Models\User;
use Database\Seeders\DefaultTranslationsSeeder;
use Database\Seeders\MenuSeeder;
use Database\Seeders\TableSeeder;

// Real-browser tests (Pest 4 + Playwright) for flows that depend on JavaScript.

beforeEach(function () {
    $this->seed([DefaultTranslationsSeeder::class, MenuSeeder::class, TableSeeder::class]);

    $this->as = function (string $role): User {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role, 'email_verified_at' => now()])->save();

        return $user;
    };
});

it('lets a client order from their table by QR code', function () {
    $table = Table::findByQrCode('z4');
    Order::create(['table_id' => $table->id, 'status' => OrderStatus::Open, 'source' => 'staff']);
    $item = MenuItem::orderBy('id')->first();

    $this->actingAs(($this->as)('client'));

    visit('/en/menu/table-z4')
        ->press('Add to cart')
        ->assertSee('Item added to cart')
        ->navigate('/en/menu/table-z4/cart')
        ->assertSee($item->t('name'))
        ->click('button:has(img[alt="+"])')
        ->press('Confirm order')
        ->assertSee('Your order was sent to the waiter')
        ->assertNoJavaScriptErrors();

    expect(Order::firstWhere('table_id', $table->id)->items()->sum('quantity'))->toEqual(2);
});

it('keeps the clicked menu category highlighted, including the last one', function () {
    $last = MenuCategory::ordered()->get()->last();
    $chip = "[data-menu-chip][href=\"#category-{$last->id}\"]";

    visit('/en/menu')
        ->resize(1440, 900)
        ->click($chip)
        ->wait(1.5)
        ->assertAttribute($chip, 'aria-current', 'true')
        ->assertNoJavaScriptErrors();
});

it('lets staff reorder menu categories by dragging', function () {
    [$first, $second] = MenuCategory::ordered()->take(2)->get()->all();

    $this->actingAs(($this->as)('staff'));

    visit('/en/staff/menu-categories')
        ->drag(
            "[data-category-id=\"{$second->id}\"] .handle-category",
            "[data-category-id=\"{$first->id}\"] .handle-category",
        )
        ->wait(1)
        ->assertNoJavaScriptErrors();

    expect($second->fresh()->sort_order)->toBeLessThan($first->fresh()->sort_order);
});

it('renders the main pages without JavaScript errors on a phone', function () {
    visit(['/en', '/en/menu', '/en/about', '/en/login', '/en/register'])
        ->on()->mobile()
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});
