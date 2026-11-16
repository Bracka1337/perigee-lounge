<?php

namespace App\Actions\Lounge;

use App\Models\Floor;
use App\Models\Room;
use App\Models\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Writes the default two-floor layout from database/data/lounge-layout.php.
 *
 * Floors and rooms are matched by their English name and tables by name, so running it
 * again updates what is there. Existing tables are moved, resized and put in their room,
 * and keep their id, capacity and active flag. Nothing is ever deleted.
 *
 * Tables listed in $updateOnly are moved when they exist but never created. The data migration
 * passes the tables of the old layout there, so one that an admin removed does not come back.
 */
class ApplyDefaultLayout
{
    /**
     * @param  list<string>  $updateOnly
     */
    public function handle(array $updateOnly = []): void
    {
        /** @var list<array{name: array<string, string>, rooms: list<array<string, mixed>>}> $layout */
        $layout = require database_path('data/lounge-layout.php');

        DB::transaction(fn () => Model::withoutEvents(function () use ($layout, $updateOnly): void {
            foreach ($layout as $floorOrder => $floorData) {
                $floor = Floor::query()->where('name->en', $floorData['name']['en'])->first() ?? new Floor;
                $floor->fill(['name' => $floorData['name'], 'sort_order' => $floorOrder])->save();

                foreach ($floorData['rooms'] as $roomOrder => $roomData) {
                    $this->writeRoom($floor, $roomOrder, $roomData, $updateOnly);
                }
            }
        }));
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $updateOnly
     */
    private function writeRoom(Floor $floor, int $order, array $data, array $updateOnly): void
    {
        /** @var array<string, string> $name */
        $name = $data['name'];

        $room = Room::query()
            ->where('floor_id', $floor->id)
            ->where('name->en', $name['en'])
            ->first() ?? new Room;

        $room->fill([
            'floor_id' => $floor->id,
            'name' => $name,
            'kind' => $data['kind'],
            'color' => $data['color'],
            'x' => $data['x'],
            'y' => $data['y'],
            'width' => $data['width'],
            'height' => $data['height'],
            'sort_order' => $order,
        ])->save();

        /** @var list<array{0: string, 1: int, 2: int|float, 3: int|float, 4: int, 5: int}> $tables */
        $tables = $data['tables'] ?? [];

        foreach ($tables as [$tableName, $capacity, $posX, $posY, $width, $height]) {
            $table = Table::query()->where('name', $tableName)->first();

            if ($table === null && in_array($tableName, $updateOnly, true)) {
                continue;
            }

            $table ??= new Table(['name' => $tableName, 'capacity' => $capacity, 'is_active' => true]);

            $table->fill([
                'room_id' => $room->id,
                'pos_x' => $posX,
                'pos_y' => $posY,
                'width' => $width,
                'height' => $height,
                'rotation' => 0,
            ])->save();
        }
    }
}
