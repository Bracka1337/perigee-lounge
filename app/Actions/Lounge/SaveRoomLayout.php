<?php

namespace App\Actions\Lounge;

use App\Models\Floor;
use App\Models\Room;
use Illuminate\Support\Facades\DB;

class SaveRoomLayout
{
    /**
     * Saves every room rectangle of one floor as the plan editor sent them: rows with an id
     * update that room, rows without one add a room. Rooms missing from the list are kept,
     * because deleting a room is its own guarded step.
     *
     * @param  list<array{id?: int|string|null, name: array<string, string|null>, kind: string, color: string, x: int|float|string, y: int|float|string, width: int|float|string, height: int|float|string}>  $rooms
     */
    public function handle(Floor $floor, array $rooms): void
    {
        DB::transaction(function () use ($floor, $rooms): void {
            foreach ($rooms as $order => $data) {
                $id = $data['id'] ?? null;

                $room = $id === null
                    ? new Room(['floor_id' => $floor->id])
                    : Room::query()->where('floor_id', $floor->id)->findOrFail((int) $id);

                $english = (string) $data['name']['en'];

                $room->fill([
                    'name' => [
                        'en' => $english,
                        'lv' => $this->orEnglish($data['name']['lv'] ?? null, $english),
                        'ru' => $this->orEnglish($data['name']['ru'] ?? null, $english),
                    ],
                    'kind' => $data['kind'],
                    'color' => $data['color'],
                    'x' => round((float) $data['x'], 2),
                    'y' => round((float) $data['y'], 2),
                    'width' => round((float) $data['width'], 2),
                    'height' => round((float) $data['height'], 2),
                    'sort_order' => $order,
                ])->save();
            }
        });
    }

    private function orEnglish(?string $value, string $english): string
    {
        return $value === null || trim($value) === '' ? $english : $value;
    }
}
