<?php

use App\Models\User;
use Database\Seeders\DefaultTranslationsSeeder;
use Database\Seeders\TableSeeder;

beforeEach(function () {
    $this->seed([DefaultTranslationsSeeder::class, TableSeeder::class]);
});

test('every booking page offers the 3D view with the lounge data', function (string $role, string $route, string $selector) {
    $user = User::factory()->create();
    $user->forceFill(['role' => $role, 'email_verified_at' => now()])->save();

    $this->actingAs($user)->get(route($route))
        ->assertOk()
        ->assertSee('data-view-toggle data-table-selector="'.$selector.'"', false)
        ->assertSee('id="loungeLayoutData"', false)
        ->assertSee('data-label-whole-floor="Whole floor"', false)
        ->assertSee('data-status="free"', false);
})->with([
    ['client', 'client.reservations.index', '.js-client-table'],
    ['staff', 'staff.reservations.index', '.js-staff-table'],
    ['staff', 'staff.orders.create', '.js-staff-table'],
]);
