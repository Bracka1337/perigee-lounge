<?php

namespace App\Enums;

enum DoorKind: string
{
    case Single = 'single';
    case Double = 'double';
    case Sliding = 'sliding';
    case Opening = 'opening'; // a gap in the wall without a door

    public function labelKey(): string
    {
        return 'door-kind-'.$this->value;
    }

    /**
     * The width a new door of this kind starts with, in metres.
     */
    public function defaultWidth(): float
    {
        return match ($this) {
            self::Single => 0.9,
            self::Double => 1.6,
            self::Sliding => 1.4,
            self::Opening => 1.8,
        };
    }
}
