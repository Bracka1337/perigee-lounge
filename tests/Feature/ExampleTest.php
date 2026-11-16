<?php

use App\Models\User;

it('redirects the root url to english', function () {
    $this->get('/')->assertRedirect('/en');
});

it('redirects paths without a locale to english', function () {
    $this->get('/about')->assertRedirect('/en/about');
});

it('falls back to english for the app and new users', function () {
    expect(config('app.locale'))->toBe('en')
        ->and(config('app.fallback_locale'))->toBe('en')
        ->and(config('site.locales'))->toBe(['en', 'lv', 'ru'])
        ->and((new User)->locale)->toBe('en');
});

it('still serves russian and latvian pages in their language', function (string $locale) {
    $this->get("/{$locale}/about")->assertOk();

    expect(app()->getLocale())->toBe($locale);
})->with(['lv', 'ru']);

it('lists english first in the language switcher', function () {
    $html = $this->get('/ru/about')->assertOk()->getContent();

    preg_match_all('/<a href="[^"]+" lang="(\w+)"/', $html, $matches);

    expect(array_slice($matches[1], 0, 3))->toBe(['en', 'lv', 'ru']);
});
