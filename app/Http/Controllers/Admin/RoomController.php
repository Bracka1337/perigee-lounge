<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Lounge\DeleteRoom;
use App\Actions\Lounge\SaveRoomLayout;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoomLayoutRequest;
use App\Models\Floor;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;

class RoomController extends Controller
{
    /**
     * Saves all rooms of one floor from the plan editor.
     */
    public function update(RoomLayoutRequest $request, string $locale, Floor $floor, SaveRoomLayout $saveRoomLayout): RedirectResponse
    {
        $saveRoomLayout->handle($floor, $request->rooms());

        return redirect()
            ->route('admin.floors.index', ['locale' => $locale, 'floor' => $floor->id])
            ->with('status', __('admin-rooms-saved'));
    }

    public function destroy(string $locale, Room $room, DeleteRoom $deleteRoom): RedirectResponse
    {
        $floorId = $room->floor_id;

        $deleteRoom->handle($room);

        return redirect()
            ->route('admin.floors.index', ['locale' => $locale, 'floor' => $floorId])
            ->with('status', __('admin-rooms-deleted'));
    }
}
