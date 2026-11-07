<?php

namespace App\Actions\Reservations;

use App\Enums\ReservationStatus;
use App\Models\Reservation;

/**
 * A booking the guest hasn't arrived for within the grace period is expired,
 * which frees its table. Runs every five minutes from the scheduler.
 */
class ExpireLateReservations
{
    public const GRACE_MINUTES = 60;

    public function handle(): int
    {
        return Reservation::where('status', ReservationStatus::Pending)
            ->where('start_at', '<', now()->subMinutes(self::GRACE_MINUTES))
            ->update(['status' => ReservationStatus::Expired]);
    }
}
