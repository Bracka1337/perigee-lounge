<?php

use App\Models\MenuItem;
use App\Models\Table;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\TableSeeder;
use Illuminate\Support\Facades\Storage;

// Seeding installs the default pictures, which must not land in the real storage folder.
beforeEach(fn () => Storage::fake('public'));

test('seeding creates the demo data', function () {
    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBe(3)
        ->and(User::where('email', 'client@perigee.test')->value('role'))->toBe('client')
        ->and(MenuItem::count())->toBeGreaterThan(0)
        ->and(Table::count())->toBeGreaterThan(0);
});

test('a failed seed leaves no users so it can be retried', function () {
    $this->app->bind(TableSeeder::class, fn () => throw new RuntimeException('Seed interrupted'));

    expect(fn () => $this->seed(DatabaseSeeder::class))->toThrow(RuntimeException::class);

    expect(User::count())->toBe(0)
        ->and(MenuItem::count())->toBe(0);
});
