<?php

namespace App\Actions\Reservations;

use App\Enums\ReservationStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\OpeningHour;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Staff books a table for a phone call or a walk-in guest. One active reservation
 * per table per day, within opening hours, and not on a table with an open order.
 */
class CreateStaffReservation
{
    /**
     * @param  array<string, mixed>  $data  Validated StoreReservationRequest data
     */
    public function handle(array $data): Reservation
    {
        $tableId = (int) $data['table_id'];
        $start = Carbon::parse($data['date'].' '.$data['time']);
        $now = Carbon::now();

        if ($start->toDateString() < $now->toDateString()
            || ($start->toDateString() === $now->toDateString() && $start->lt($now))) {
            throw new BusinessRuleException('staff-reservations-error-past');
        }

        $table = Table::where('is_active', true)->findOrFail($tableId);

        if ($table->is_occupied) {
            throw new BusinessRuleException('staff-reservations-error-table-has-open-order');
        }

        if (Order::active()->where('table_id', $tableId)->exists()) {
            Log::warning('Staff Reservation blocked: active order exists', ['table_id' => $tableId]);

            throw new BusinessRuleException('staff-reservations-error-table-has-open-order');
        }

        if (! OpeningHour::isOpenAt($start)) {
            throw new BusinessRuleException('staff-reservations-error-closed');
        }

        $tableTaken = Reservation::where('table_id', $tableId)
            ->whereDate('start_at', $start->toDateString())
            ->whereIn('status', ReservationStatus::holdingTable())
            ->exists();

        if ($tableTaken) {
            throw new BusinessRuleException('staff-reservations-error-overlap');
        }

        return Reservation::create([
            'user_id' => null,
            'table_id' => $tableId,
            'guest_name' => ($data['guest_name'] ?? '') !== '' ? $data['guest_name'] : null,
            'guest_phone' => ($data['guest_phone'] ?? '') !== '' ? $data['guest_phone'] : null,
            'start_at' => $start,
            'end_at' => null,
            'status' => ReservationStatus::Pending,
            'guests_count' => $data['guests_count'],
            'source' => $data['mode'],
        ]);
    }
}
