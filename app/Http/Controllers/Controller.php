<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

abstract class Controller
{
    /**
     * The day in the ?date= filter, or today if it is missing or not a real Y-m-d date.
     */
    protected function dateFilter(Request $request): Carbon
    {
        $date = $request->query('date');

        if (is_string($date) && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return Carbon::parse($date)->startOfDay();
        }

        return Carbon::today();
    }
}
