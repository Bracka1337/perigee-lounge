<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Models\Floor;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Table;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * Which tables are taken on a day. A table booking holds its table; a whole-room booking holds
 * every table in its room. Every page that shows availability and every booking check asks here.
 */
class TableHolds
{
    /**
     * Reservations that hold tables on the date, with the room and its tables loaded.
     *
     * @return EloquentCollection<int, Reservation>
     */
    public function on(string $date): EloquentCollection
    {
        return Reservation::query()
            ->with('room.tables')
            ->whereDate('start_at', $date)
            ->whereIn('status', ReservationStatus::holdingTable())
            ->get();
    }

    /**
     * The reservations per table id, with a room booking listed under every table of its room.
     *
     * @param  iterable<Reservation>  $reservations
     * @return Collection<int, Collection<int, Reservation>>
     */
    public function byTable(iterable $reservations): Collection
    {
        $byTable = [];
        foreach ($reservations as $reservation) {
            $tableIds = $reservation->room_id !== null && $reservation->room !== null
                ? $reservation->room->tables->pluck('id')->push($reservation->table_id)->unique()
                : collect([$reservation->table_id]);

            foreach ($tableIds as $tableId) {
                $byTable[(int) $tableId][] = $reservation;
            }
        }

        return collect($byTable)->map(fn (array $list) => collect($list));
    }

    /**
     * Whether a table is held on the date, by its own booking or by a booking of its room.
     */
    public function tableHeld(int $tableId, string $date): bool
    {
        return $this->byTable($this->on($date))->has($tableId);
    }

    /**
     * Whether any table of the room is held on the date, has an open order now, or is
     * occupied, so the room cannot be booked as a whole.
     */
    public function roomTaken(Room $room, string $date): bool
    {
        $tableIds = $room->tables()->pluck('id');
        if ($tableIds->isEmpty()) {
            return true;
        }

        $held = $this->byTable($this->on($date))->keys();

        return $tableIds->intersect($held)->isNotEmpty()
            || Table::query()->whereIn('id', $tableIds)->where('is_occupied', true)->exists()
            || Order::query()->whereIn('table_id', $tableIds)->whereNotIn('status', OrderStatus::closed())->exists();
    }

    /**
     * For every room that can be booked as a whole: its seats, whether it can be booked on the
     * date, and whether someone already booked it whole.
     *
     * @param  iterable<Floor>  $floors  floors with their rooms and tables loaded (LoungeLayout)
     * @return array<int, array{seats: int, taken: bool, booked: bool}>
     */
    public function roomsOn(iterable $floors, string $date): array
    {
        $holding = $this->on($date);
        $held = $this->byTable($holding)->keys()->all();
        $booked = $holding->whereNotNull('room_id')->pluck('room_id')->all();
        $busy = Order::query()->whereNotIn('status', OrderStatus::closed())->pluck('table_id')
            ->merge(Table::query()->where('is_occupied', true)->pluck('id'))->all();

        $rooms = [];
        foreach ($floors as $floor) {
            foreach ($floor->rooms->where('is_bookable_whole', true) as $room) {
                $tables = $floor->tables->where('room_id', $room->id);
                $ids = $tables->pluck('id')->all();

                $rooms[$room->id] = [
                    'seats' => (int) $tables->sum('capacity'),
                    'taken' => $ids === [] || array_intersect($ids, $held) !== [] || array_intersect($ids, $busy) !== [],
                    'booked' => in_array($room->id, $booked, true),
                ];
            }
        }

        return $rooms;
    }

    /**
     * The table a room booking is filed under: the room's largest active table.
     */
    public function leadTable(Room $room): ?Table
    {
        return $room->tables()->where('is_active', true)->orderByDesc('capacity')->orderBy('id')->first();
    }
}
