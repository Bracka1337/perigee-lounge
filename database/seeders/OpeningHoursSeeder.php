<?php

namespace Database\Seeders;

use App\Models\OpeningHour;
use Illuminate\Database\Seeder;

class OpeningHoursSeeder extends Seeder
{
    public function run(): void
    {
        if (OpeningHour::count() > 0) {
            return;
        }

        $schedule = [
            1 => ['18:00:00', '00:00:00'],
            2 => ['18:00:00', '00:00:00'],
            3 => ['18:00:00', '00:00:00'],
            4 => ['18:00:00', '00:00:00'],
            5 => ['18:00:00', '02:00:00'],
            6 => ['18:00:00', '02:00:00'],
            0 => ['18:00:00', '00:00:00'],
        ];


        foreach ($schedule as $weekday => [$open, $close]) {
            OpeningHour::create([
                'weekday' => $weekday,
                'is_open' => true,
                'opens_at' => $open,
                'closes_at' => $close,
            ]);
        }
    }
}
