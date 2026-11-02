<?php

use App\Models\User;
use Database\Seeders\DefaultTranslationsSeeder;

beforeEach(function () {
    $this->seed(DefaultTranslationsSeeder::class);
});

test('guests see the login link', function () {
    $this->get('/en')
        ->assertOk()
        ->assertSee('href="'.url('/en/login').'"', false)
        ->assertDontSee(url('/en/logout'), false);
});

test('signed in users see their dashboard link and logout', function (string $role, string $path) {
    $this->actingAs(User::factory()->create(['role' => $role]))
        ->get('/en')
        ->assertOk()
        ->assertSee('href="'.url($path).'"', false)
        ->assertSee('action="'.url('/en/logout').'"', false);

    $this->get($path)->assertOk();
})->with([
    ['client', '/en/dashboard'],
    ['staff', '/en/staff/dashboard'],
    ['admin', '/en/admin/dashboard'],
]);

test('language links keep the current page', function () {
    $this->get('/en/about')
        ->assertOk()
        ->assertSee('href="'.url('/lv/about').'"', false)
        ->assertSee('href="'.url('/ru/about').'"', false);
});

test('unknown pages return 404 in every locale', function (string $locale) {
    $this->get("/{$locale}/does-not-exist")->assertNotFound();
})->with(['en', 'lv', 'ru']);

test('wrong role gets 403 with a working error page', function () {
    $this->actingAs(User::factory()->create(['role' => 'client']))
        ->get('/en/admin/dashboard')
        ->assertForbidden();
});
