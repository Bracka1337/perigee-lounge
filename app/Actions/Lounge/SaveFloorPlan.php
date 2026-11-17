<?php

namespace App\Actions\Lounge;

use App\Exceptions\BusinessRuleException;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Table;
use App\Services\LoungeRenders;
use Illuminate\Support\Facades\DB;

/**
 * Saves one floor from the floor plan editor: its rooms and the tables on it, in one go.
 *
 * Rooms with an id are updated, rooms without one are added, and rooms of the floor that are
 * missing from the list are deleted. Tables are put in the room that contains their centre.
 * Tables are never deleted here: deleting a table also deletes its reservations, so it is a
 * separate, confirmed step.
 */
class SaveFloorPlan
{
    public function __construct(private LoungeRenders $renders) {}

    /**
     * @param  array<int, array{id?: int|string|null, name: array<string, string|null>, kind: string, color: string, is_bookable_whole?: bool|int|string, x: int|float|string, y: int|float|string, width: int|float|string, height: int|float|string}>  $rooms
     * @param  array<int, array{id?: int|string|null, name: string, capacity: int|string, x: int|float|string, y: int|float|string, width: int|string, height: int|string, rotation: int|string, is_active: bool|int|string}>  $tables
     */
    public function handle(Floor $floor, array $rooms, array $tables): void
    {
        $deleted = DB::transaction(function () use ($floor, $rooms, $tables): array {
            $existing = $floor->rooms()->get()->keyBy('id');
            $saved = [];

            foreach (array_values($rooms) as $order => $data) {
                $id = isset($data['id']) ? (int) $data['id'] : null;
                $room = $id === null ? new Room(['floor_id' => $floor->id]) : $existing->get($id);
                if ($room === null) {
                    continue;
                }

                $english = trim((string) $data['name']['en']);
                $room->fill([
                    'name' => [
                        'en' => $english,
                        'lv' => $this->orEnglish($data['name']['lv'] ?? null, $english),
                        'ru' => $this->orEnglish($data['name']['ru'] ?? null, $english),
                    ],
                    'kind' => $data['kind'],
                    'color' => $data['color'],
                    'is_bookable_whole' => filter_var($data['is_bookable_whole'] ?? false, FILTER_VALIDATE_BOOL),
                    'x' => round((float) $data['x'], 2),
                    'y' => round((float) $data['y'], 2),
                    'width' => round((float) $data['width'], 2),
                    'height' => round((float) $data['height'], 2),
                    'sort_order' => $order,
                ])->save();

                $saved[] = $room;
            }

            $sentTableIds = [];
            foreach ($tables as $data) {
                $table = isset($data['id']) ? Table::query()->lockForUpdate()->findOrFail((int) $data['id']) : new Table;
                $x = round((float) $data['x'], 2);
                $y = round((float) $data['y'], 2);
                $room = collect($saved)->first(fn (Room $room) => $room->contains($x, $y));

                if ($room === null) {
                    throw new BusinessRuleException('admin-plan-table-outside-generic');
                }

                $table->fill([
                    'name' => trim($data['name']),
                    'capacity' => (int) $data['capacity'],
                    'room_id' => $room->id,
                    'pos_x' => $x,
                    'pos_y' => $y,
                    'width' => (int) $data['width'],
                    'height' => (int) $data['height'],
                    'rotation' => (int) $data['rotation'],
                    'is_active' => filter_var($data['is_active'], FILTER_VALIDATE_BOOL),
                ])->save();

                $sentTableIds[] = $table->id;
            }

            $keptIds = array_map(fn (Room $room) => $room->id, $saved);
            $removed = $existing->except($keptIds);

            // A table that was not in the plan still stands in its old room. That room stays.
            if (Table::query()->whereIn('room_id', $removed->modelKeys())->whereNotIn('id', $sentTableIds)->exists()) {
                throw new BusinessRuleException('admin-rooms-delete-has-tables');
            }

            $removed->each(fn (Room $room) => $room->delete());

            return $removed->all();
        });

        foreach ($deleted as $room) {
            $this->renders->forget($room);
        }
    }

    private function orEnglish(?string $value, string $english): string
    {
        return $value === null || trim($value) === '' ? $english : trim($value);
    }
}
