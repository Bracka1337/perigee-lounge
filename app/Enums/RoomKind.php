<?php

namespace App\Enums;

/**
 * What a room is used for. Only seating rooms appear in the booking legend.
 */
enum RoomKind: string
{
    case Seating = 'seating';
    case Bar = 'bar';
    case Entrance = 'entrance';
    case Stairs = 'stairs';
    case Service = 'service';

    public function labelKey(): string
    {
        return 'room-kind-'.$this->value;
    }
}
