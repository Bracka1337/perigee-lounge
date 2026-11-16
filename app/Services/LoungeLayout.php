<?php

namespace App\Services;

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
        $floors = Floor::query()->with('rooms')->orderBy('sort_order')->orderBy('id')->get();

        if ($floors->isEmpty()) {
            $placeholder = (new Floor)->forceFill(['id' => 0, 'name' => [], 'sort_order' => 0]);
            $placeholder->setRelation('rooms', new Collection);
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
