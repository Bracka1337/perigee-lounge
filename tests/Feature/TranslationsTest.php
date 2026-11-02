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

test('activity log uses the same labels as its filters', function () {
    $this->seed(DefaultTranslationsSeeder::class);
    app()->setLocale('en');

    $log = new ActivityLog(['action' => 'created', 'model_type' => 'App\Models\PromoCode']);

    expect($log->action_name)->toBe(__('logs-action-created'))
        ->and($log->model_name)->toBe(__('logs-model-promocode'))
        ->and(ActivityLog::modelLabel(null))->toBe('');
});

test('sync adds nothing if it fails partway', function () {
    Translation::saving(function () {
        if (Translation::count() === 5) {
            throw new RuntimeException('Stopped');
        }
    });

    expect(fn () => $this->artisan('translations:sync')->run())->toThrow(RuntimeException::class);

    expect(Translation::count())->toBe(0);
});
