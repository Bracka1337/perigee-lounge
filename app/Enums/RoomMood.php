<?php

namespace App\Enums;

/**
 * How a room is lit in the 3D view: the colour and strength of its own soft light, and how
 * strongly its floor and neon strips glow.
 */
enum RoomMood: string
{
    case Warm = 'warm';      // amber lounge light
    case Neon = 'neon';      // the room's own colour, strong neon
    case Cinema = 'cinema';  // dim and blue, the screen is the brightest thing
    case Bright = 'bright';  // clear white light, for bars

    public function labelKey(): string
    {
        return 'room-mood-'.$this->value;
    }
}
