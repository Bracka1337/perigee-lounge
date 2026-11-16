<?php

namespace App\Actions\Lounge;

use App\Models\Room;
use App\Models\Table;

class SaveTable
{
    /**
     * Creates or updates a table from the admin form. When the table is put in another
     * room and its position lies outside that room, it moves to the middle of the room,
     * so it never shows up on the wrong floor or in a walkway.
     *
     * @param  array<string, mixed>  $data  validated form values
     */
    public function handle(Table $table, array $data, bool $isActive): Table
    {
        $table->fill([
            'name' => $data['name'],
            'room_id' => $data['room_id'] ?? null,
            'capacity' => $data['capacity'],
            'pos_x' => $data['pos_x'] ?? $table->pos_x ?? 10,
            'pos_y' => $data['pos_y'] ?? $table->pos_y ?? 10,
            'width' => $data['width'] ?? $table->width ?? 80,
            'height' => $data['height'] ?? $table->height ?? 80,
            'rotation' => $data['rotation'] ?? $table->rotation ?? 0,
            'is_active' => $isActive,
        ]);

        $room = $table->room_id === null ? null : Room::query()->find($table->room_id);

        if ($room !== null && $table->isDirty('room_id') && ! $room->contains($table->pos_x, $table->pos_y)) {
            $table->pos_x = round($room->x + $room->width / 2, 2);
            $table->pos_y = round($room->y + $room->height / 2, 2);
        }

        $table->save();

        return $table;
    }
}
