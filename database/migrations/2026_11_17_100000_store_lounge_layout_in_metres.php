<?php

use App\Actions\Lounge\ApplyDefaultLayout;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The plan used to be a fixed box: positions were percentages of a 30 x 26.25 m plan and
     * table sizes pixels of a 1200 px wide drawing. From here on everything is in real units:
     * room rectangles and table centres in metres, table footprints in centimetres.
     */
    private const PLAN_WIDTH = 30;

    private const PLAN_DEPTH = 26.25;

    private const CM_PER_PX = 2.5;

    /**
     * Tables of the first, one-floor layout. They are moved, never created again.
     */
    private const GROUND_TABLES = [
        'T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'B1', 'B2', 'B3', 'B4', 'B5', 'B6', 'B7',
        'Z1', 'Z2', 'Z3', 'Z4', 'Z5', 'Z6', 'Z7', 'Z8', 'P1', 'P2', 'VIP',
    ];

    /**
     * Tables the second floor brought in.
     */
    private const SECOND_FLOOR_TABLES = [
        'L1', 'L2', 'L3', 'L4', 'C1', 'C2', 'C3', 'U1', 'U2', 'S1', 'S2', 'S3', 'S4', 'K1', 'K2',
    ];

    public function up(): void
    {
        if (! DB::table('tables')->exists()) {
            return; // a fresh database gets the layout from the seeders
        }

        // A database that already has floors got the second floor's tables before. Any of
        // them that is gone now was removed by an admin, so it is not created again.
        $hadFloors = DB::table('floors')->exists();

        $this->convert(self::PLAN_WIDTH / 100, self::PLAN_DEPTH / 100, self::CM_PER_PX);

        // Rooms and tables of the default layout move to the new layout without gaps. Rooms
        // and tables an admin added keep their place, now in metres. Ids never change.
        app(ApplyDefaultLayout::class)->handle($hadFloors
            ? [...self::GROUND_TABLES, ...self::SECOND_FLOOR_TABLES]
            : self::GROUND_TABLES);
    }

    public function down(): void
    {
        $this->convert(100 / self::PLAN_WIDTH, 100 / self::PLAN_DEPTH, 1 / self::CM_PER_PX);
        DB::table('rooms')->where('kind', 'corridor')->whereNotExists(
            fn ($query) => $query->select(DB::raw(1))->from('tables')->whereColumn('tables.room_id', 'rooms.id')
        )->delete();
    }

    private function convert(float $xFactor, float $yFactor, float $sizeFactor): void
    {
        DB::transaction(function () use ($xFactor, $yFactor, $sizeFactor): void {
            foreach (DB::table('rooms')->get(['id', 'x', 'y', 'width', 'height']) as $room) {
                DB::table('rooms')->where('id', $room->id)->update([
                    'x' => round((float) $room->x * $xFactor, 2),
                    'y' => round((float) $room->y * $yFactor, 2),
                    'width' => round((float) $room->width * $xFactor, 2),
                    'height' => round((float) $room->height * $yFactor, 2),
                ]);
            }

            foreach (DB::table('tables')->get(['id', 'pos_x', 'pos_y', 'width', 'height']) as $table) {
                DB::table('tables')->where('id', $table->id)->update([
                    'pos_x' => round((float) $table->pos_x * $xFactor, 2),
                    'pos_y' => round((float) $table->pos_y * $yFactor, 2),
                    'width' => max(1, (int) round((int) $table->width * $sizeFactor)),
                    'height' => max(1, (int) round((int) $table->height * $sizeFactor)),
                ]);
            }
        });
    }
};
