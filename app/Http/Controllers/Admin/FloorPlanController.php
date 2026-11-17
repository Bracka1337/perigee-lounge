<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Lounge\SaveFloorPlan;
use App\Enums\DoorKind;
use App\Enums\FixtureType;
use App\Enums\RoomColor;
use App\Enums\RoomKind;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FloorPlanRequest;
use App\Models\Door;
use App\Models\Fixture;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Table;
use App\Services\LoungeLayout;
use App\Services\Render\RenderStudio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The one admin page for the lounge layout: floors, rooms and tables on a single plan.
 */
class FloorPlanController extends Controller
{
    public function show(Request $request, string $locale, LoungeLayout $layout, RenderStudio $studio): View
    {
        $floors = $layout->floors(activeTablesOnly: false)->filter(fn (Floor $floor) => $floor->exists)->values();
        $active = $floors->firstWhere('id', $request->integer('floor')) ?? $floors->first();
        $index = $active === null ? 0 : (int) $floors->search(fn (Floor $floor) => $floor->id === $active->id);

        return view('admin.floor-plan.index', [
            'floors' => $floors,
            'activeFloor' => $active,
            'editor' => $active === null ? null : [
                'floor' => ['id' => $active->id, 'name' => $active->t('name')],
                'rooms' => $active->rooms->map(fn (Room $room) => [
                    'id' => $room->id,
                    'name' => ['en' => $room->name['en'] ?? '', 'lv' => $room->name['lv'] ?? '', 'ru' => $room->name['ru'] ?? ''],
                    'kind' => $room->kind->value,
                    'color' => $room->color->value,
                    'is_bookable_whole' => $room->is_bookable_whole,
                    'x' => $room->x,
                    'y' => $room->y,
                    'width' => $room->width,
                    'height' => $room->height,
                ])->values(),
                'doors' => $active->doors->map(fn (Door $door) => [
                    'id' => $door->id, 'kind' => $door->kind->value, 'orientation' => $door->orientation,
                    'x' => $door->x, 'y' => $door->y, 'width' => $door->width,
                ])->values(),
                'fixtures' => $active->fixtures->map(fn (Fixture $fixture) => [
                    'id' => $fixture->id, 'type' => $fixture->type->value, 'x' => $fixture->x, 'y' => $fixture->y,
                    'rotation' => $fixture->rotation, 'width' => $fixture->width, 'depth' => $fixture->depth, 'points' => $fixture->points,
                ])->values(),
                'tables' => $active->tables->map(fn (Table $table) => [
                    'id' => $table->id,
                    'name' => $table->name,
                    'capacity' => $table->capacity,
                    'x' => $table->pos_x,
                    'y' => $table->pos_y,
                    'width' => $table->width,
                    'height' => $table->height,
                    'rotation' => $table->rotation,
                    'is_active' => $table->is_active,
                ])->values(),
                // The floor below, drawn faintly so walls and stairs can be lined up.
                'below' => $index > 0 ? ($floors->get($index - 1)?->rooms->map(fn (Room $room) => [
                    'x' => $room->x, 'y' => $room->y, 'width' => $room->width, 'height' => $room->height,
                ])->values() ?? []) : [],
                'frame' => $layout->frame($floors)->toArray(),
                'draft' => $request->old('plan_payload'),
                'colors' => collect(RoomColor::cases())->mapWithKeys(fn (RoomColor $color) => [$color->value => [
                    'hex' => $color->hex(), 'label' => __($color->labelKey()),
                ]]),
                'kinds' => collect(RoomKind::cases())->mapWithKeys(fn (RoomKind $kind) => [$kind->value => __($kind->labelKey())]),
                'doorKinds' => collect(DoorKind::cases())->mapWithKeys(fn (DoorKind $kind) => [$kind->value => [
                    'label' => __($kind->labelKey()), 'width' => $kind->defaultWidth(),
                ]]),
                'fixtureTypes' => collect(FixtureType::cases())->mapWithKeys(fn (FixtureType $type) => [$type->value => [
                    'label' => __($type->labelKey()), 'group' => $type->group(), 'size' => $type->size(),
                ]]),
                'fixtureGroups' => collect(['entertainment', 'bar', 'decor'])->mapWithKeys(fn (string $group) => [$group => __('fixture-group-'.$group)]),
                'otherFloors' => $floors->reject(fn (Floor $floor) => $floor->id === $active->id)
                    ->map(fn (Floor $floor) => ['id' => $floor->id, 'name' => $floor->t('name'), 'rooms' => $floor->rooms
                        ->reject(fn (Room $room) => $room->kind === RoomKind::Corridor)
                        ->map(fn (Room $room) => ['id' => $room->id, 'name' => $room->t('name')])->values()])
                    ->values(),
                'urls' => [
                    'tableUpdate' => route('admin.tables.update', ['locale' => $locale, 'table' => '__ID__']),
                    'tableDestroy' => route('admin.tables.destroy', ['locale' => $locale, 'table' => '__ID__']),
                ],
            ],
            'lounge3d' => $layout->toArray($floors),
            'studioEnabled' => $studio->enabled(),
        ]);
    }

    public function update(FloorPlanRequest $request, string $locale, Floor $floor, SaveFloorPlan $save): RedirectResponse
    {
        $save->handle($floor, $request->rooms(), $request->tables(), $request->doors(), $request->fixtures());

        return redirect()
            ->route('admin.floor-plan', ['locale' => $locale, 'floor' => $floor->id])
            ->with('status', __('admin-plan-saved'));
    }
}
