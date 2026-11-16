<?php

namespace App\Actions\Lounge;

use App\Models\Floor;
use Illuminate\Support\Facades\DB;

class MoveFloor
{
    /**
     * Moves a floor one place up or down the list and renumbers the whole list,
     * so equal or missing sort values can never get in the way.
     */
    public function handle(Floor $floor, string $direction): void
    {
        DB::transaction(function () use ($floor, $direction): void {
            $ids = array_values(Floor::query()->orderBy('sort_order')->orderBy('id')->pluck('id')->all());

            $from = array_search($floor->id, $ids, true);
            if ($from === false) {
                return;
            }

            $to = $direction === 'up' ? $from - 1 : $from + 1;
            if (! array_key_exists($to, $ids)) {
                return;
            }

            [$ids[$from], $ids[$to]] = [$ids[$to], $ids[$from]];

            foreach ($ids as $position => $id) {
                Floor::query()->whereKey($id)->update(['sort_order' => $position]);
            }
        });
    }
}
