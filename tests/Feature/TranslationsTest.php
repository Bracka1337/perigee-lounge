<?php

use App\Models\ActivityLog;
use App\Models\Translation;
use App\Models\User;
use Database\Seeders\DefaultTranslationsSeeder;

test('sync adds missing translations', function () {
    $this->artisan('translations:sync')->assertSuccessful();

    expect(Translation::where('key', 'table-not-found')->exists())->toBeTrue();
});

test('sync keeps edited translations', function () {
    $this->seed(DefaultTranslationsSeeder::class);

    Translation::where('key', 'header-home')->update(['value' => ['en' => 'Edited', 'lv' => 'Edited', 'ru' => 'Edited']]);
    Translation::where('key', 'table-not-found')->delete();

    $this->artisan('translations:sync')->assertSuccessful();

    expect(Translation::where('key', 'header-home')->first()->value['en'])->toBe('Edited')
        ->and(Translation::where('key', 'table-not-found')->exists())->toBeTrue();
});

test('every seeded translation has all three languages', function () {
    foreach ((new DefaultTranslationsSeeder)->items() as $key => $values) {
        expect(array_keys($values))->toEqualCanonicalizing(['en', 'lv', 'ru'], "{$key} is missing a language");
    }
});

test('activity log labels follow the locale', function () {
    $this->seed(DefaultTranslationsSeeder::class);
    app()->setLocale('en');

    $log = new ActivityLog(['action' => 'updated', 'model_type' => 'App\\Models\\Order']);

    expect($log->action_name)->toBe('Updated')
        ->and($log->model_name)->toBe('Order')
        ->and(ActivityLog::modelLabel('App\\Models\\Unknown'))->toBe('Unknown');
});

test('table errors follow the locale', function () {
    $this->seed(DefaultTranslationsSeeder::class);

    $this->actingAs(User::factory()->create())
        ->get('/en/menu/table-unknown')
        ->assertRedirect('/en/menu')
        ->assertSessionHas('error', 'Table not found');
});
