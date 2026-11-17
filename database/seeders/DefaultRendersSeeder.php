<?php

namespace Database\Seeders;

use App\Services\Render\DefaultRenders;
use Illuminate\Database\Seeder;

/**
 * Installs the generated pictures from database/seeders/renders for rooms, tables and menu
 * items that have none. Nothing that already has a picture is replaced.
 */
class DefaultRendersSeeder extends Seeder
{
    public function run(DefaultRenders $defaults): void
    {
        $defaults->install();
    }
}
