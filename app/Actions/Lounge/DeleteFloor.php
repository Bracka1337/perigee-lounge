<?php

namespace App\Actions\Lounge;

use App\Exceptions\BusinessRuleException;
use App\Models\Floor;

class DeleteFloor
{
    /**
     * A floor that still has rooms stays: its rooms have to be moved out or deleted first.
     */
    public function handle(Floor $floor): void
    {
        if ($floor->rooms()->exists()) {
            throw new BusinessRuleException('admin-floors-delete-has-rooms');
        }

        $floor->delete();
    }
}
