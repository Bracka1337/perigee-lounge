<?php

use App\Actions\Lounge\ApplyDefaultLayout;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The tables of the layout before this migration. They are moved, never created again.
     */
    private const OLD_TABLES = [
        'T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7',
        'B1', 'B2', 'B3', 'B4', 'B5', 'B6', 'B7',
        'Z1', 'Z2', 'Z3', 'Z4', 'Z5', 'Z6', 'Z7', 'Z8',
        'P1', 'P2', 'VIP',
    ];

    /**
     * Moves the tables of an existing lounge into the two-floor layout. Tables keep their
     * ids and names, because reservations and orders point to them. A database with no
     * tables is left alone: it gets the layout from the seeders. A table of the old layout
     * that was removed since stays removed.
     */
    public function up(): void
    {
        // The layout file is now in metres. This migration only matters for databases from
        // before floors existed. The metres migration that follows puts everything right.
        if (! DB::table('tables')->exists()) {
            return;
        }

        app(ApplyDefaultLayout::class)->handle(self::OLD_TABLES);
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
