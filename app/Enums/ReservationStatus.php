<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    /**
     * Booked or seated: the reservation still holds its table.
     *
     * @return list<self>
     */
    public static function holdingTable(): array
    {
        return [self::Pending, self::Active];
    }
}
