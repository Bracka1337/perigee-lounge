<?php

namespace App\Actions\Orders;

use App\Models\Order;
use App\Models\Reservation;
use Illuminate\Support\Carbon;

/**
 * Seats a reservation's guest and gives them an order: the reservation's own order,
 * a walk-in order already open on the table (which gets linked), or a new one.
 * Returns null when the table's open order belongs to another reservation.
 */
class OpenReservationOrder
{
    public function handle(Reservation $reservation): ?Order
    {
        if ($reservation->status === Reservation::STATUS_PENDING) {
            $reservation->status = Reservation::STATUS_ACTIVE;
            $reservation->save();
        }

        $order = $reservation->order ?? $this->linkOrCreate($reservation);

        if ($order === null) {
            return null;
        }

        $reservation->table?->occupy();

        return $order;
    }

    private function linkOrCreate(Reservation $reservation): ?Order
    {
        $source = ($reservation->source ?? '') !== '' ? $reservation->source : 'reservation';

        $existing = Order::active()->where('table_id', $reservation->table_id)->first();

        if ($existing === null) {
            return Order::create([
                'reservation_id' => $reservation->id,
                'table_id' => $reservation->table_id,
                'user_id' => $reservation->user_id,
                'status' => Order::STATUS_OPEN,
                'started_at' => Carbon::now(),
                'source' => $source,
            ]);
        }

        if ($existing->reservation_id !== null) {
            return null;
        }

        $existing->reservation_id = $reservation->id;
        if (($existing->source ?? '') === '') {
            $existing->source = $source;
        }
        $existing->save();

        return $existing;
    }
}
