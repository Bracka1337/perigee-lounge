<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    public function run(): void
    {

        $tables = [
            ['name' => 'T1', 'capacity' => 4, 'pos_x' => 30.00, 'pos_y' => 9.00, 'width' => 150, 'height' => 80, 'rotation' => 0, 'is_active' => true],
            ['name' => 'T2', 'capacity' => 4, 'pos_x' => 30.00, 'pos_y' => 18.00, 'width' => 150, 'height' => 80, 'rotation' => 0, 'is_active' => true],
            ['name' => 'T3', 'capacity' => 6, 'pos_x' => 32.80, 'pos_y' => 27.00, 'width' => 220, 'height' => 80, 'rotation' => 0, 'is_active' => true],
            ['name' => 'T4', 'capacity' => 6, 'pos_x' => 32.80, 'pos_y' => 36.00, 'width' => 220, 'height' => 80, 'rotation' => 0, 'is_active' => true],
            ['name' => 'T5', 'capacity' => 4, 'pos_x' => 53.50, 'pos_y' => 36.00, 'width' => 140, 'height' => 80, 'rotation' => 0, 'is_active' => true],
            ['name' => 'T6', 'capacity' => 4, 'pos_x' => 53.50, 'pos_y' => 27.00, 'width' => 140, 'height' => 80, 'rotation' => 0, 'is_active' => true],
            ['name' => 'T7', 'capacity' => 3, 'pos_x' => 55.00, 'pos_y' => 9.00, 'width' => 100, 'height' => 80, 'rotation' => 0, 'is_active' => true],
            ['name' => 'B1', 'capacity' => 4, 'pos_x' => 6.00, 'pos_y' => 50.00, 'width' => 100, 'height' => 80, 'rotation' => 0, 'is_active' => true],
            ['name' => 'B2', 'capacity' => 4, 'pos_x' => 6.00, 'pos_y' => 59.00, 'width' => 100, 'height' => 80, 'rotation' => 0, 'is_active' => true],
            ['name' => 'B3', 'capacity' => 4, 'pos_x' => 6.00, 'pos_y' => 68.00, 'width' => 100, 'height' => 80, 'rotation' => 0, 'is_active' => true],
            ['name' => 'B4', 'capacity' => 4, 'pos_x' => 6.00, 'pos_y' => 77.00, 'width' => 100, 'height' => 80, 'rotation' => 0, 'is_active' => true],
            ['name' => 'B5', 'capacity' => 4, 'pos_x' => 17.00, 'pos_y' => 77.00, 'width' => 100, 'height' => 80, 'rotation' => 0, 'is_active' => true],
            ['name' => 'B6', 'capacity' => 4, 'pos_x' => 17.00, 'pos_y' => 68.00, 'width' => 100, 'height' => 80, 'rotation' => 0, 'is_active' => true],
            ['name' => 'B7', 'capacity' => 4, 'pos_x' => 17.00, 'pos_y' => 50.00, 'width' => 100, 'height' => 80, 'rotation' => 0, 'is_active' => true],
            ['name' => 'Z1', 'capacity' => 4, 'pos_x' => 29.57, 'pos_y' => 50.00, 'width' => 140, 'height' => 80, 'rotation' => 0, 'is_active' => true],
            ['name' => 'Z2', 'capacity' => 4, 'pos_x' => 27.57, 'pos_y' => 70.00, 'width' => 91, 'height' => 159, 'rotation' => 0, 'is_active' => true],
            ['name' => 'Z3', 'capacity' => 2, 'pos_x' => 27.80, 'pos_y' => 90.00, 'width' => 100, 'height' => 100, 'rotation' => 0, 'is_active' => true],
            ['name' => 'Z4', 'capacity' => 2, 'pos_x' => 55.00, 'pos_y' => 55.00, 'width' => 100, 'height' => 100, 'rotation' => 0, 'is_active' => true],
            ['name' => 'Z5', 'capacity' => 4, 'pos_x' => 40.00, 'pos_y' => 73.80, 'width' => 140, 'height' => 80, 'rotation' => 0, 'is_active' => true],
            ['name' => 'Z6', 'capacity' => 4, 'pos_x' => 53.50, 'pos_y' => 73.80, 'width' => 140, 'height' => 80, 'rotation' => 0, 'is_active' => true],
            ['name' => 'Z7', 'capacity' => 4, 'pos_x' => 70.50, 'pos_y' => 73.80, 'width' => 140, 'height' => 80, 'rotation' => 0, 'is_active' => true],
            ['name' => 'Z8', 'capacity' => 4, 'pos_x' => 70.50, 'pos_y' => 83.00, 'width' => 140, 'height' => 80, 'rotation' => 0, 'is_active' => true],
            ['name' => 'P1', 'capacity' => 4, 'pos_x' => 82.00, 'pos_y' => 54.00, 'width' => 80, 'height' => 120, 'rotation' => 0, 'is_active' => true],
            ['name' => 'P2', 'capacity' => 4, 'pos_x' => 95.04, 'pos_y' => 45.50, 'width' => 80, 'height' => 120, 'rotation' => 0, 'is_active' => true],
            ['name' => 'VIP', 'capacity' => 20, 'pos_x' => 90.00, 'pos_y' => 82.40, 'width' => 210, 'height' => 260, 'rotation' => 0, 'is_active' => true],
        ];

        foreach ($tables as $data) {
            Table::updateOrCreate(['name' => $data['name']], $data);
        }
    }
}
