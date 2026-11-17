<?php

namespace App\Enums;

/**
 * What a room is used for. Corridors are the walkways between rooms: they have no walls of
 * their own, so the floor has no gaps. Only rooms with tables appear in the booking legend.
 */
enum RoomKind: string
{
    case Seating = 'seating';
    case Bar = 'bar';
    case Entrance = 'entrance';
    case Stairs = 'stairs';
    case Service = 'service';
    case Corridor = 'corridor';

    public function labelKey(): string
    {
        return 'room-kind-'.$this->value;
    }
}
