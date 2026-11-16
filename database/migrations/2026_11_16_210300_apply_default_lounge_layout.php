<?php

use App\Actions\Lounge\ApplyDefaultLayout;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Moves the tables of an existing lounge into the two-floor layout. Tables keep their
     * ids and names, because reservations and orders point to them. A database with no
     * tables is left alone: it gets the layout from the seeders.
     */
    public function up(): void
    {
        if (! DB::table('tables')->exists()) {
            return;
        }

        app(ApplyDefaultLayout::class)->handle();
    }

    /**
     * Table positions from before the migration are not kept, so there is nothing to restore.
     * Dropping the floors and rooms tables in the earlier migrations removes the rest.
     */
    public function down(): void
    {
        DB::table('tables')->update(['room_id' => null]);
    }
};
