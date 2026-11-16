<?php

namespace App\Enums;

/**
 * The fixed palette rooms are drawn in, on the plan and in the legend.
 */
enum RoomColor: string
{
    case Violet = 'violet';
    case Amber = 'amber';
    case Teal = 'teal';
    case Rose = 'rose';
    case Sky = 'sky';
    case Lime = 'lime';
    case Coral = 'coral';
    case Slate = 'slate';

    public function hex(): string
    {
        return match ($this) {
            self::Violet => '#B9A5FF',
            self::Amber => '#F5B860',
            self::Teal => '#5ED6C4',
            self::Rose => '#F28BB0',
            self::Sky => '#7CB8F5',
            self::Lime => '#A8D672',
            self::Coral => '#F59A7C',
            self::Slate => '#8A8FB0',
        };
    }

    public function labelKey(): string
    {
        return 'room-color-'.$this->value;
    }
}
