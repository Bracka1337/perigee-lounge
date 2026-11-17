<?php

namespace App\Actions\Reservations;

use App\Enums\ReservationStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\OpeningHour;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\TableHolds;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Books a whole room for one guest. The room must allow it, and none of its tables may be
 * booked, occupied or have an open order that day. The booking is filed under the room's
 * largest table and holds every table of the room for the day.
 */
class CreateRoomReservation
{
    public function __construct(private TableHolds $holds) {}

    /**
     * @param  array{user_id?: int|null, guest_name?: string|null, guest_phone?: string|null, source: string}  $guest
     */
    public function handle(Room $room, Carbon $start, int $guests, array $guest): Reservation
    {
        if (! $room->is_bookable_whole) {
            throw new BusinessRuleException('room-booking-error-not-bookable');
        }

        if (! OpeningHour::isOpenAt($start)) {
            throw new BusinessRuleException('client-reservations-error-closed');
        }

        $seats = (int) $room->tables()->where('is_active', true)->sum('capacity');
        if ($guests > $seats) {
            throw new BusinessRuleException('room-booking-error-too-many-guests');
        }

        return DB::transaction(function () use ($room, $start, $guests, $guest): Reservation {
            // Lock the room's tables, so two guests cannot book the same room at once.
            $room->tables()->lockForUpdate()->get();

            $lead = $this->holds->leadTable($room);
            if ($lead === null || $this->holds->roomTaken($room, $start->toDateString())) {
                throw new BusinessRuleException('room-booking-error-taken');
            }

            return Reservation::create([
                'user_id' => $guest['user_id'] ?? null,
                'table_id' => $lead->id,
                'room_id' => $room->id,
                'guest_name' => $guest['guest_name'] ?? null,
                'guest_phone' => $guest['guest_phone'] ?? null,
                'start_at' => $start,
                'end_at' => null,
                'status' => ReservationStatus::Pending,
                'guests_count' => $guests,
                'source' => $guest['source'],
            ]);
        });
    }
}
