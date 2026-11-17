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
    private const LIGHT_TYPES = ['pendant_light', 'floor_lamp', 'neon_strip', 'led_strip'];

    /**
     * @param  list<string>  $updateOnly
     */
    public function handle(array $updateOnly = []): void
    {
        $layout = $this->layout();

        DB::transaction(fn () => Model::withoutEvents(function () use ($layout, $updateOnly): void {
            foreach ($layout as $floorOrder => $floorData) {
                $floor = Floor::query()->where('name->en', $floorData['name']['en'])->first() ?? new Floor;
                $floor->fill(['name' => $floorData['name'], 'sort_order' => $floorOrder])->save();

                foreach ($floorData['rooms'] as $roomOrder => $roomData) {
                    $this->writeRoom($floor, $roomOrder, $roomData, $updateOnly);
                }

                $this->furnishFloor($floor, $floorData);
            }
        }));
    }

    /**
     * Gives the default floors their doors and fixtures, without touching rooms or tables.
     * A floor that already has doors or fixtures is left as it is.
     */
    public function furnish(): void
    {
        DB::transaction(fn () => Model::withoutEvents(function (): void {
            foreach ($this->layout() as $floorData) {
                $floor = Floor::query()->where('name->en', $floorData['name']['en'])->first();
                if ($floor !== null) {
                    $this->furnishFloor($floor, $floorData);
                }
            }
        }));
    }

    /**
     * Adds the default lights to default floors that have no lights yet, for lounges that were
     * furnished before lights existed.
     */
    public function addLights(): void
    {
        DB::transaction(fn () => Model::withoutEvents(function (): void {
            foreach ($this->layout() as $floorData) {
                $floor = Floor::query()->where('name->en', $floorData['name']['en'])->first();
                if ($floor === null || $floor->fixtures()->whereIn('type', self::LIGHT_TYPES)->exists()) {
                    continue;
                }

                foreach ($floorData['fixtures'] ?? [] as $fixture) {
                    if (in_array($fixture[0], self::LIGHT_TYPES, true)) {
                        $this->createFixture($floor, $fixture);
                    }
                }
            }
        }));
    }

    /**
     * @return list<array{name: array<string, string>, rooms: list<array<string, mixed>>, doors?: list<array{0: string, 1: string, 2: int|float, 3: int|float, 4: int|float}>, fixtures?: list<array{0: string, 1: int|float, 2: int|float, 3: int, 4: int|float, 5: int|float, 6: list<array{0: int|float, 1: int|float}>|null, 7?: array{color: string, strength: int}}>}>
     */
    private function layout(): array
    {
        /** @var list<array{name: array<string, string>, rooms: list<array<string, mixed>>, doors?: list<array{0: string, 1: string, 2: int|float, 3: int|float, 4: int|float}>, fixtures?: list<array{0: string, 1: int|float, 2: int|float, 3: int, 4: int|float, 5: int|float, 6: list<array{0: int|float, 1: int|float}>|null, 7?: array{color: string, strength: int}}>}> $layout */
        $layout = require database_path('data/lounge-layout.php');

        return $layout;
    }

    /**
     * @param  array{doors?: list<array{0: string, 1: string, 2: int|float, 3: int|float, 4: int|float}>, fixtures?: list<array{0: string, 1: int|float, 2: int|float, 3: int, 4: int|float, 5: int|float, 6: list<array{0: int|float, 1: int|float}>|null, 7?: array{color: string, strength: int}}>}  $data
     */
    private function furnishFloor(Floor $floor, array $data): void
    {
        if ($floor->doors()->exists() || $floor->fixtures()->exists()) {
            return;
        }

        foreach ($data['doors'] ?? [] as [$kind, $orientation, $x, $y, $width]) {
            $floor->doors()->create(['kind' => $kind, 'orientation' => $orientation, 'x' => $x, 'y' => $y, 'width' => $width]);
        }

        foreach ($data['fixtures'] ?? [] as $fixture) {
            $this->createFixture($floor, $fixture);
        }
    }

    /**
     * @param  array{0: string, 1: int|float, 2: int|float, 3: int, 4: int|float, 5: int|float, 6: list<array{0: int|float, 1: int|float}>|null, 7?: array{color: string, strength: int}}  $fixture
     */
    private function createFixture(Floor $floor, array $fixture): void
    {
        [$type, $x, $y, $rotation, $width, $depth, $points] = $fixture;

        $floor->fixtures()->create([
            'type' => $type, 'x' => $x, 'y' => $y, 'rotation' => $rotation, 'width' => $width, 'depth' => $depth,
            'points' => $points === null ? null : array_map(fn (array $point) => [(float) $point[0], (float) $point[1]], $points),
            'settings' => $fixture[7] ?? null,
        ]);
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
            'mood' => $data['mood'] ?? 'warm',
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
