<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Lounge\DeleteFloor;
use App\Actions\Lounge\MoveFloor;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FloorRequest;
use App\Http\Requests\Admin\MoveFloorRequest;
use App\Models\Floor;
use Illuminate\Http\RedirectResponse;

class FloorController extends Controller
{
    public function store(FloorRequest $request, string $locale): RedirectResponse
    {
        $floor = Floor::create([
            'name' => $request->names(),
            'sort_order' => ((int) Floor::max('sort_order')) + 1,
        ]);

        return redirect()
            ->route('admin.floor-plan', ['locale' => $locale, 'floor' => $floor->id])
            ->with('status', __('admin-floors-created'));
    }

    public function update(FloorRequest $request, string $locale, Floor $floor): RedirectResponse
    {
        $floor->update(['name' => $request->names()]);

        return redirect()
            ->route('admin.floor-plan', ['locale' => $locale, 'floor' => $floor->id])
            ->with('status', __('admin-floors-updated'));
    }

    public function move(MoveFloorRequest $request, string $locale, Floor $floor, MoveFloor $moveFloor): RedirectResponse
    {
        $moveFloor->handle($floor, $request->string('direction')->value());

        return redirect()->route('admin.floor-plan', ['locale' => $locale, 'floor' => $floor->id]);
    }

    public function destroy(string $locale, Floor $floor, DeleteFloor $deleteFloor): RedirectResponse
    {
        $deleteFloor->handle($floor);

        return redirect()
            ->route('admin.floor-plan', ['locale' => $locale])
            ->with('status', __('admin-floors-deleted'));
    }
}
