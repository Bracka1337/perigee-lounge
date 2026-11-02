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
