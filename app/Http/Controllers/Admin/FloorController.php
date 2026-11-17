<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Lounge\DeleteFloor;
use App\Actions\Lounge\MoveFloor;
use App\Enums\RoomColor;
use App\Enums\RoomKind;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FloorRequest;
use App\Http\Requests\Admin\MoveFloorRequest;
use App\Models\Floor;
use App\Services\LoungeLayout;
use App\Services\Render\RenderStudio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FloorController extends Controller
{
    public function index(Request $request, string $locale, LoungeLayout $layout, RenderStudio $studio): View
    {
        $floors = $layout->floors(activeTablesOnly: false)->filter(fn (Floor $floor) => $floor->exists)->values();

        return view('admin.floors.index', [
            'floors' => $floors,
            'lounge3d' => $layout->toArray($floors),
            'activeFloor' => $floors->firstWhere('id', $request->integer('floor')) ?? $floors->first(),
            'kinds' => RoomKind::cases(),
            'colors' => RoomColor::cases(),
            'studioEnabled' => $studio->enabled(),
        ]);
    }

    public function store(FloorRequest $request, string $locale): RedirectResponse
    {
        $floor = Floor::create([
            'name' => $request->names(),
            'sort_order' => ((int) Floor::max('sort_order')) + 1,
        ]);

        return redirect()
            ->route('admin.floors.index', ['locale' => $locale, 'floor' => $floor->id])
            ->with('status', __('admin-floors-created'));
    }

    public function update(FloorRequest $request, string $locale, Floor $floor): RedirectResponse
    {
        $floor->update(['name' => $request->names()]);

        return redirect()
            ->route('admin.floors.index', ['locale' => $locale, 'floor' => $floor->id])
            ->with('status', __('admin-floors-updated'));
    }

    public function move(MoveFloorRequest $request, string $locale, Floor $floor, MoveFloor $moveFloor): RedirectResponse
    {
        $moveFloor->handle($floor, $request->string('direction')->value());

        return redirect()->route('admin.floors.index', ['locale' => $locale, 'floor' => $floor->id]);
    }

    public function destroy(string $locale, Floor $floor, DeleteFloor $deleteFloor): RedirectResponse
    {
        $deleteFloor->handle($floor);

        return redirect()
            ->route('admin.floors.index', ['locale' => $locale])
            ->with('status', __('admin-floors-deleted'));
    }
}
