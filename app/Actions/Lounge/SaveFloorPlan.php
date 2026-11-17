<?php

namespace App\Actions\Lounge;

use App\Enums\FixtureType;
use App\Exceptions\BusinessRuleException;
use App\Models\Door;
use App\Models\Fixture;
use App\Models\Floor;
use App\Models\Reservation;
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
     * @param  array<int, array{id?: int|string|null, name: array<string, string|null>, kind: string, color: string, mood?: string, is_bookable_whole?: bool|int|string, x: int|float|string, y: int|float|string, width: int|float|string, height: int|float|string}>  $rooms
     * @param  array<int, array{id?: int|string|null, name: string, capacity: int|string, x: int|float|string, y: int|float|string, width: int|string, height: int|string, rotation: int|string, is_active: bool|int|string}>  $tables
     * @param  array<int, array{id?: int|string|null, kind: string, orientation: string, x: int|float|string, y: int|float|string, width: int|float|string}>|null  $doors
     * @param  array<int, array{id?: int|string|null, type: string, x: int|float|string, y: int|float|string, rotation: int|string, width: int|float|string, depth: int|float|string, points?: list<array{0: int|float|string, 1: int|float|string}>|null, settings?: array{color?: string, strength?: int|string}|null}>|null  $fixtures
     */
    public function handle(Floor $floor, array $rooms, array $tables, ?array $doors = null, ?array $fixtures = null): void
    {
        $deleted = DB::transaction(function () use ($floor, $rooms, $tables, $doors, $fixtures): array {
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
                    'mood' => $data['mood'] ?? $room->mood ?? 'warm',
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

            // Whole-room bookings point at their room, so a booked room stays.
            if (Reservation::query()->whereIn('room_id', $removed->modelKeys())->exists()) {
                throw new BusinessRuleException('admin-rooms-delete-has-bookings');
            }

            $removed->each(fn (Room $room) => $room->delete());

            // Doors and fixtures are left alone when the plan does not include them.
            if ($doors !== null) {
                $this->syncDoors($floor, $doors);
            }
            if ($fixtures !== null) {
                $this->syncFixtures($floor, $fixtures);
            }

            return $removed->all();
        });

        foreach ($deleted as $room) {
            $this->renders->forget($room);
        }
    }

    /**
     * @param  array<int, array{id?: int|string|null, kind: string, orientation: string, x: int|float|string, y: int|float|string, width: int|float|string}>  $doors
     */
    private function syncDoors(Floor $floor, array $doors): void
    {
        $existing = $floor->doors()->get()->keyBy('id');
        $kept = [];

        foreach ($doors as $data) {
            $door = isset($data['id']) ? $existing->get((int) $data['id']) : null;
            $door ??= new Door(['floor_id' => $floor->id]);
            $door->fill([
                'kind' => $data['kind'],
                'orientation' => $data['orientation'],
                'x' => round((float) $data['x'], 2),
                'y' => round((float) $data['y'], 2),
                'width' => round((float) $data['width'], 2),
            ])->save();
            $kept[] = $door->id;
        }

        $floor->doors()->whereNotIn('id', $kept)->get()->each(fn (Door $door) => $door->delete());
    }

    /**
     * @param  array<int, array{id?: int|string|null, type: string, x: int|float|string, y: int|float|string, rotation: int|string, width: int|float|string, depth: int|float|string, points?: list<array{0: int|float|string, 1: int|float|string}>|null, settings?: array{color?: string, strength?: int|string}|null}>  $fixtures
     */
    private function syncFixtures(Floor $floor, array $fixtures): void
    {
        $existing = $floor->fixtures()->get()->keyBy('id');
        $kept = [];

        foreach ($fixtures as $data) {
            $fixture = isset($data['id']) ? $existing->get((int) $data['id']) : null;
            $fixture ??= new Fixture(['floor_id' => $floor->id]);
            $points = $data['points'] ?? null;
            $fixture->fill([
                'type' => $data['type'],
                'x' => round((float) $data['x'], 2),
                'y' => round((float) $data['y'], 2),
                'rotation' => (int) $data['rotation'],
                'width' => round((float) $data['width'], 2),
                'depth' => round((float) $data['depth'], 2),
                // Only lights keep settings: their colour and strength.
                'settings' => FixtureType::from($data['type'])->isLight() ? [
                    'color' => $data['settings']['color'] ?? 'amber',
                    'strength' => (int) ($data['settings']['strength'] ?? 5),
                ] : null,
                'points' => $points === null || $points === [] ? null
                    : array_map(fn (array $point) => [round((float) $point[0], 2), round((float) $point[1], 2)], $points),
            ])->save();
            $kept[] = $fixture->id;
        }

        $floor->fixtures()->whereNotIn('id', $kept)->get()->each(fn (Fixture $fixture) => $fixture->delete());
    }

    private function orEnglish(?string $value, string $english): string
    {
        return $value === null || trim($value) === '' ? $english : trim($value);
    }
}
