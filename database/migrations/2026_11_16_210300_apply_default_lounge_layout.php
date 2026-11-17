<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * This used to move the tables of an existing lounge into the first two-floor layout.
     */
    public function up(): void
    {
        // Superseded by 2026_11_17_100000_store_lounge_layout_in_metres, which converts the old
        // percentage positions and then applies the layout file (now in metres). Applying the
        // file here as well would write metres that the next migration converts a second time.
        // Databases that already ran this migration keep the result; nothing else to do.
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
