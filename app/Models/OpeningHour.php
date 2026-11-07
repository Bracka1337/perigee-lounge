<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class OpeningHour extends Model
{
    protected $fillable = [
        'weekday',
        'is_open',
        'opens_at',
        'closes_at',
    ];

    protected $casts = [
        'is_open' => 'boolean',
    ];

    /**
     * Whether the venue is open at the given moment. Handles closing after midnight
     * (opens 18:00, closes 02:00) and treats a 00:00 closing time as end of day.
     */
    public static function isOpenAt(CarbonInterface $moment): bool
    {
        $hours = self::where('weekday', $moment->dayOfWeek)->first();

        if ($hours === null || ! $hours->is_open) {
            return false;
        }

        $opensAt = $hours->opens_at;
        $closesAt = $hours->closes_at === '00:00:00' ? '23:59:59' : $hours->closes_at;

        if ($opensAt === null || $opensAt === '' || $closesAt === null || $closesAt === '') {
            return true;
        }

        $time = $moment->format('H:i:s');

        return $closesAt > $opensAt
            ? $time >= $opensAt && $time < $closesAt
            : $time >= $opensAt || $time < $closesAt;
    }
}
