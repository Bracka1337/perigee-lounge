<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Open = 'open';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * @return list<self>
     */
    public static function closed(): array
    {
        return [self::Completed, self::Cancelled];
    }
}
