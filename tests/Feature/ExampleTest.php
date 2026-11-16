<?php

use App\Models\MenuCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('redirects the root url to English', function () {
    $this->get('/')->assertRedirect('/en');
});

it('redirects paths without a locale to English', function () {
    $this->get('/about')->assertRedirect('/en/about');
    $this->get('/menu/table-z4')->assertRedirect('/en/menu');
});

it('lists English first in the language switcher and as the x-default link', function () {
    $html = (string) $this->get('/ru/about')->assertOk()->getContent();

    preg_match_all('/<a href="[^"]+" lang="(\w+)"/', $html, $matches);

    expect(array_slice($matches[1], 0, 3))->toBe(['en', 'lv', 'ru'])
        ->and($html)->toContain('hreflang="x-default" href="'.url('/en/about').'"');
});

it('gives new accounts English unless they chose another language', function () {
    DB::table('users')->insert([
        'name' => 'Column Default',
        'email' => 'column-default@example.com',
        'password' => 'not-used',
    ]);

    expect(User::where('email', 'column-default@example.com')->value('locale'))->toBe('en')
        ->and((new User)->locale)->toBe('en')
        ->and(User::factory()->create(['locale' => 'ru'])->refresh()->locale)->toBe('ru');
});

it('falls back to the English text when a translation is missing', function () {
    $category = MenuCategory::create(['name' => ['ru' => 'Чай', 'en' => 'Tea'], 'sort_order' => 0]);

    app()->setLocale('lv');

    expect($category->t('name'))->toBe('Tea');
});
