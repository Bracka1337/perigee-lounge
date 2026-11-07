<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    /**
     * Clients cancel their own reservations. Staff cancel the ones they took by phone
     * or for walk-ins; a client's own booking is left for the client to cancel.
     */
    public function cancel(User $user, Reservation $reservation): bool
    {
        if ($user->isWorker()) {
            return $reservation->user_id === null;
        }

        return $reservation->user_id === $user->id;
    }
}
