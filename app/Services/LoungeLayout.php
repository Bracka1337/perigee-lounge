<?php

namespace App\Services;

use App\Models\Door;
use App\Models\Fixture;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Table;
use Illuminate\Database\Eloquent\Collection;

/**
 * Loads the lounge as floors with their rooms and tables, for every page that draws the plan.
 */
class LoungeLayout
{
    /**
     * Floors in display order. Each has its `rooms` and `tables` relations set, and every
     * table has its `room`. A table without a room is shown on the first floor. A lounge
     * with no floors yet still gets one unsaved floor, so its tables stay bookable.
     *
     * @return Collection<int, Floor>
     */
    public function floors(bool $activeTablesOnly = true): Collection
    {
        $floors = Floor::query()->with(['rooms', 'doors', 'fixtures'])->orderBy('sort_order')->orderBy('id')->get();

        if ($floors->isEmpty()) {
            $placeholder = (new Floor)->forceFill(['id' => 0, 'name' => [], 'sort_order' => 0]);
            $placeholder->setRelation('rooms', new Collection);
            $placeholder->setRelation('doors', new Collection);
            $placeholder->setRelation('fixtures', new Collection);
            $floors->push($placeholder);
        }

        /** @var array<int, Room> $rooms */
        $rooms = [];
        /** @var array<int, int> $floorOfRoom */
        $floorOfRoom = [];
        foreach ($floors as $floor) {
            foreach ($floor->rooms as $room) {
                $rooms[$room->id] = $room;
                $floorOfRoom[$room->id] = $floor->id;
            }
        }

        /** @var array<int, list<Table>> $tablesByFloor */
        $tablesByFloor = [];
        $firstFloorId = $floors->firstOrFail()->id;

        $tables = Table::query()
            ->when($activeTablesOnly, fn ($query) => $query->where('is_active', true))
            ->orderBy('id')
            ->get();

        foreach ($tables as $table) {
            $table->setRelation('room', $rooms[$table->room_id] ?? null);
            $tablesByFloor[$floorOfRoom[$table->room_id] ?? $firstFloorId][] = $table;
        }

        foreach ($floors as $floor) {
            $floor->setRelation('tables', new Collection($tablesByFloor[$floor->id] ?? []));
        }

        return $floors;
    }

    /**
     * The rectangle around the building on every floor, so all floors are drawn to one scale.
     *
     * @param  Collection<int, Floor>  $floors
     */
    public function frame(Collection $floors): PlanFrame
    {
        return PlanFrame::around(
            $floors->flatMap(fn (Floor $floor) => $floor->rooms),
            $floors->flatMap(fn (Floor $floor) => $floor->tables),
        );
    }

    /**
     * The layout as plain data for the 3D view, which builds its scene from the same
     * floors, rooms and tables as the 2D plan. Names are in the current language.
     *
     * @param  Collection<int, Floor>  $floors
     * @return array{frame: array{x: float, y: float, width: float, depth: float}, floors: array<int, array<string, mixed>>}
     */
    public function toArray(Collection $floors): array
    {
        return [
            'frame' => $this->frame($floors)->toArray(),
            'floors' => $floors->values()->map(fn (Floor $floor) => [
                'id' => $floor->id,
                'name' => (string) $floor->t('name'),
                'rooms' => $floor->rooms->values()->map(fn (Room $room) => [
                    'id' => $room->id,
                    'name' => (string) $room->t('name'),
                    'kind' => $room->kind->value,
                    'bookableWhole' => $room->is_bookable_whole,
                    'mood' => $room->mood->value,
                    'color' => $room->color->hex(),
                    'x' => $room->x,
                    'y' => $room->y,
                    'width' => $room->width,
                    'height' => $room->height,
                ])->all(),
                'tables' => $floor->tables->values()->map(fn (Table $table) => [
                    'id' => $table->id,
                    'name' => $table->name,
                    'capacity' => $table->capacity,
                    'roomId' => $table->room_id,
                    'x' => $table->pos_x,
                    'y' => $table->pos_y,
                    'width' => $table->width,
                    'height' => $table->height,
                    'rotation' => $table->rotation,
                    'active' => $table->is_active,
                ])->all(),
                'doors' => $floor->doors->map(fn (Door $door) => [
                    'kind' => $door->kind->value,
                    'orientation' => $door->orientation,
                    'x' => $door->x,
                    'y' => $door->y,
                    'width' => $door->width,
                ])->values()->all(),
                'fixtures' => $floor->fixtures->map(fn (Fixture $fixture) => [
                    'type' => $fixture->type->value,
                    'x' => $fixture->x,
                    'y' => $fixture->y,
                    'rotation' => $fixture->rotation,
                    'width' => $fixture->width,
                    'depth' => $fixture->depth,
                    'points' => $fixture->points,
                    'settings' => $fixture->settings,
                ])->values()->all(),
            ])->all(),
        ];
    }

    /**
     * The floor to open first: the floor of the table that is already selected (after a
     * failed form submit), else the requested floor if it exists, else the first one.
     *
     * @param  Collection<int, Floor>  $floors
     */
    public function activeFloorId(Collection $floors, mixed $requested, mixed $selectedTableId = null): int
    {
        if (is_numeric($selectedTableId)) {
            $ofTable = $floors->first(fn (Floor $floor) => $floor->tables->contains('id', (int) $selectedTableId));

            if ($ofTable !== null) {
                return $ofTable->id;
            }
        }

        $requested = is_numeric($requested) ? (int) $requested : null;

        return $requested !== null && $floors->contains('id', $requested)
            ? $requested
            : $floors->firstOrFail()->id;
    }
}
