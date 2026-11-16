<?php

namespace App\Actions\Lounge;

use App\Exceptions\BusinessRuleException;
use App\Models\Room;

class DeleteRoom
{
    /**
     * A room that still has tables stays: reservations and orders point to those tables,
     * so they have to be moved to another room first.
     */
    public function handle(Room $room): void
    {
        if ($room->tables()->exists()) {
            throw new BusinessRuleException('admin-rooms-delete-has-tables');
        }

        $room->delete();
    }
}
