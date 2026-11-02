<?php

use Database\Seeders\DefaultTranslationsSeeder;

beforeEach(function () {
    $this->seed(DefaultTranslationsSeeder::class);
});

test('home page renders translated copy for each locale', function (string $locale, string $title) {
    $this->get("/{$locale}")
        ->assertOk()
        ->assertSee($title)
        ->assertSee("<html lang=\"{$locale}\"", false);
})->with([
    ['en', 'The closest point to quiet.'],
    ['lv', 'Tuvākais punkts klusumam.'],
    ['ru', 'Ближайшая точка к тишине.'],
]);

test('about page renders its sections', function () {
    $this->get('/en/about')
        ->assertOk()
        ->assertSee('The room')
        ->assertSee('House rules');
});

test('contacts page shows contact details', function () {
    $this->get('/en/contacts')
        ->assertOk()
        ->assertSee('12 Example Street, Exampleville')
        ->assertSee('hello@perigee-lounge.example');
});

test('contacts page hides the map and social links until they are configured', function () {
    $this->get('/en/contacts')
        ->assertOk()
        ->assertDontSee('<iframe', false)
        ->assertDontSee('Instagram');
});
