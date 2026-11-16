<?php

namespace Database\Seeders;

use App\Actions\Lounge\ApplyDefaultLayout;
use Illuminate\Database\Seeder;

/**
 * Seeds both floors of the lounge with their rooms and tables.
 * The layout itself lives in database/data/lounge-layout.php.
 */
class TableSeeder extends Seeder
{
    public function run(ApplyDefaultLayout $applyDefaultLayout): void
    {
        $applyDefaultLayout->handle();
    }
}
