<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Lounge\SaveTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TableRequest;
use App\Models\Table;
use App\Services\LoungeRenders;
use Illuminate\Http\RedirectResponse;

/**
 * Tables are placed and edited on the floor plan page. These are the two steps that change a
 * table outside one floor: moving it to a room on another floor, and deleting it.
 */
class TableController extends Controller
{
    public function update(TableRequest $request, string $locale, Table $table, SaveTable $saveTable): RedirectResponse
    {
        $saveTable->handle($table, $request->validated(), $request->boolean('is_active'));

        return redirect()
            ->route('admin.floor-plan', ['locale' => $locale, 'floor' => $table->room?->floor_id])
            ->with('status', __('admin-tables-updated'));
    }

    /**
     * Deleting a table also deletes its reservations and orders (the foreign keys cascade),
     * which is why the floor plan asks for confirmation first.
     */
    public function destroy(string $locale, Table $table, LoungeRenders $renders): RedirectResponse
    {
        $floorId = $table->room?->floor_id;
        $table->delete();
        $renders->forget($table);

        return redirect()
            ->route('admin.floor-plan', ['locale' => $locale, 'floor' => $floorId])
            ->with('status', __('admin-tables-deleted'));
    }
}
