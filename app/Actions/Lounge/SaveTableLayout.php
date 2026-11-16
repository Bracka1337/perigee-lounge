<?php

namespace App\Actions\Lounge;

use App\Models\Floor;
use App\Models\Room;
use App\Models\Table;
use Illuminate\Support\Facades\DB;

class SaveTableLayout
{
    /**
     * Saves table positions from the layout editor. A table dropped inside another room
     * of its floor now belongs to that room. A table left in a walkway keeps its room.
     *
     * @param  list<array{id: int|string, pos_x: int|float|string, pos_y: int|float|string, width: int|float|string, height: int|float|string, rotation: int|float|string}>  $items
     */
    public function handle(array $items): void
    {
        $floors = Floor::query()->with('rooms')->orderBy('sort_order')->orderBy('id')->get();
        $firstFloor = $floors->first();

        DB::transaction(function () use ($items, $floors, $firstFloor): void {
            foreach ($items as $row) {
                $table = Table::query()->find((int) $row['id']);
                if ($table === null) {
                    continue;
                }

                $posX = (float) $row['pos_x'];
                $posY = (float) $row['pos_y'];

                $floor = $floors->first(fn (Floor $floor) => $floor->rooms->contains('id', $table->room_id)) ?? $firstFloor;
                $room = $floor?->rooms->first(fn (Room $room) => $room->contains($posX, $posY));

                $table->update([
                    'pos_x' => $posX,
                    'pos_y' => $posY,
                    'width' => (int) round((float) $row['width']),
                    'height' => (int) round((float) $row['height']),
                    'rotation' => (int) round((float) $row['rotation']),
                    'room_id' => $room === null ? $table->room_id : $room->id,
                ]);
            }
        });
    }
}
