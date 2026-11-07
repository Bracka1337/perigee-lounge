<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
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
        $order = $reservation->order ?? $this->linkOrCreate($reservation);

        // Refused: the guest isn't seated, so the reservation stays as it was.
        if ($order === null) {
            return null;
        }

        if ($reservation->status === ReservationStatus::Pending) {
            $reservation->status = ReservationStatus::Active;
            $reservation->save();
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
                'status' => OrderStatus::Open,
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
