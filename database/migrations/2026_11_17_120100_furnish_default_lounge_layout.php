<?php

use App\Actions\Lounge\ApplyDefaultLayout;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The cabins upstairs as the previous layout drew them: [x, y, width, height] and the centre
     * of their table. Cabins 2 and 4 could only be reached through another cabin.
     */
    private const OLD_CABINS = [
        'Cabin 1' => [[21, 6.5, 4.5, 7.25], 'S1'],
        'Cabin 2' => [[25.5, 6.5, 4.5, 7.25], 'S2'],
        'Cabin 3' => [[21, 13.75, 4.5, 7.25], 'S3'],
        'Cabin 4' => [[25.5, 13.75, 4.5, 7.25], 'S4'],
    ];

    private const NEW_CABINS = [
        'Cabin 1' => [[21, 6.5, 4.5, 6.75], [23.25, 9.88]],
        'Cabin 2' => [[25.5, 6.5, 4.5, 6.75], [27.75, 9.88]],
        'Cabin 3' => [[21, 14.75, 4.5, 6.25], [23.25, 17.88]],
        'Cabin 4' => [[25.5, 14.75, 4.5, 6.25], [27.75, 17.88]],
    ];

    /**
     * Existing lounges get the doors and fixtures of the default layout on floors that have
     * none yet. The upstairs cabins get a corridor between their two rows, so every cabin has
     * its own door, but only while they still sit exactly where the default layout put them.
     */
    public function up(): void
    {
        if (! DB::table('tables')->exists()) {
            return; // a fresh database gets everything from the seeders
        }

        DB::transaction(function (): void {
            $floorId = DB::table('floors')->where('name->en', 'Second floor')->value('id');
            if ($floorId !== null && $this->cabinsUnchanged((int) $floorId)) {
                $this->moveCabins((int) $floorId);
            }
        });

        app(ApplyDefaultLayout::class)->furnish();
    }

    public function down(): void
    {
        DB::table('doors')->delete();
        DB::table('fixtures')->delete();
    }

    private function cabinsUnchanged(int $floorId): bool
    {
        foreach (self::OLD_CABINS as $name => [[$x, $y, $width, $height]]) {
            $room = DB::table('rooms')->where('floor_id', $floorId)->where('name->en', $name)->first(['x', 'y', 'width', 'height']);
            $same = $room !== null && abs((float) $room->x - $x) < 0.001 && abs((float) $room->y - $y) < 0.001
                && abs((float) $room->width - $width) < 0.001 && abs((float) $room->height - $height) < 0.001;
            if (! $same) {
                return false;
            }
        }

        return ! DB::table('rooms')->where('floor_id', $floorId)
            ->where('x', '<', 30)->where('x', '>=', 21)->where('y', '>', 13)->where('y', '<', 15)
            ->whereNotIn('name->en', array_keys(self::OLD_CABINS))->exists();
    }

    private function moveCabins(int $floorId): void
    {
        foreach (self::NEW_CABINS as $name => [[$x, $y, $width, $height], [$tableX, $tableY]]) {
            $room = DB::table('rooms')->where('floor_id', $floorId)->where('name->en', $name);
            $roomId = $room->value('id');
            $room->update(['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);
            DB::table('tables')->where('room_id', $roomId)->where('name', self::OLD_CABINS[$name][1])->update(['pos_x' => $tableX, 'pos_y' => $tableY]);
        }

        DB::table('rooms')->insert([
            'floor_id' => $floorId,
            'name' => json_encode(['en' => 'Cabin corridor', 'lv' => 'Kabīņu gaitenis', 'ru' => 'Коридор кабин'], JSON_UNESCAPED_UNICODE),
            'kind' => 'corridor',
            'color' => 'slate',
            'is_bookable_whole' => false,
            'x' => 21, 'y' => 13.25, 'width' => 9, 'height' => 1.5,
            'sort_order' => 50,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
