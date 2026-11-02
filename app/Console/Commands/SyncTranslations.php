<?php

namespace App\Console\Commands;

use App\Models\Translation;
use Database\Seeders\DefaultTranslationsSeeder;
use Illuminate\Console\Command;

class SyncTranslations extends Command
{
    protected $signature = 'translations:sync';

    protected $description = 'Add translation keys that are missing from the database, without changing existing ones';

    public function handle(DefaultTranslationsSeeder $seeder): int
    {
        $existing = Translation::query()->pluck('key')->all();
        $missing = array_diff_key($seeder->items(), array_flip($existing));

        foreach ($missing as $key => $values) {
            Translation::create(['key' => $key, 'value' => $seeder->normalize($key, $values)]);
            Translation::forgetCache($key);
        }

        $this->info('Added '.count($missing).' missing translations.');

        return self::SUCCESS;
    }
}
