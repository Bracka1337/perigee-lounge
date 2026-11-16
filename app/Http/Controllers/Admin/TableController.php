<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Lounge\SaveTable;
use App\Actions\Lounge\SaveTableLayout;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TableRequest;
use App\Models\Floor;
use App\Models\OpeningHour;
use App\Models\Table;
use App\Services\LoungeLayout;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TableController extends Controller
{
    public function index(Request $request, string $locale, LoungeLayout $layout): View
    {
        $floors = $layout->floors(activeTablesOnly: false);

        return view('admin.tables.index', [
            'floors' => $floors,
            'activeFloorId' => $layout->activeFloorId($floors, $request->query('floor')),
            'tables' => Table::with('room')->orderBy('id')->get(),
            'hours' => OpeningHour::orderBy('weekday')->get()->keyBy('weekday'),
        ]);
    }

    public function create(string $locale): View
    {
        return view('admin.tables.create', [
            'floors' => $this->floorsWithRooms(),
        ]);
    }

    public function store(TableRequest $request, string $locale, SaveTable $saveTable): RedirectResponse
    {
        $saveTable->handle(new Table, $request->validated(), $request->boolean('is_active'));

        return redirect()->route('admin.tables.index', ['locale' => $locale])
            ->with('status', __('admin-tables-created'));
    }

    public function edit(string $locale, Table $table): View
    {
        return view('admin.tables.edit', [
            'table' => $table,
            'floors' => $this->floorsWithRooms(),
        ]);
    }

    public function update(TableRequest $request, string $locale, Table $table, SaveTable $saveTable): RedirectResponse
    {
        $saveTable->handle($table, $request->validated(), $request->boolean('is_active'));

        return redirect()->route('admin.tables.index', ['locale' => $locale])
            ->with('status', __('admin-tables-updated'));
    }

    public function destroy(string $locale, Table $table): RedirectResponse
    {
        $table->delete();

        return redirect()->route('admin.tables.index', ['locale' => $locale])
            ->with('status', __('admin-tables-deleted'));
    }

    public function updateOpeningHours(Request $request, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'hours' => ['required', 'array:0,1,2,3,4,5,6'],
            'hours.*' => ['array:is_open,opens_at,closes_at'],
            'hours.*.is_open' => ['nullable', 'boolean'],
            'hours.*.opens_at' => ['nullable', 'date_format:H:i'],
            'hours.*.closes_at' => ['nullable', 'date_format:H:i'],
        ]);

        foreach ($data['hours'] as $weekday => $row) {
            $model = OpeningHour::firstOrNew(['weekday' => (int) $weekday]);
            $model->is_open = isset($row['is_open']) ? (bool) $row['is_open'] : false;
            $model->opens_at = ($row['opens_at'] ?? '') !== '' ? $row['opens_at'].':00' : null;
            $model->closes_at = ($row['closes_at'] ?? '') !== '' ? $row['closes_at'].':00' : null;
            $model->save();
        }

        return redirect()->route('admin.tables.index', ['locale' => $locale])
            ->with('status', __('admin-tables-opening-hours-updated'));
    }

    public function updateLayout(Request $request, string $locale, SaveTableLayout $saveTableLayout): RedirectResponse
    {
        $data = $request->validate([
            'layout_payload' => ['required', 'string'],
        ]);

        $items = Validator::make(
            ['layout' => json_decode($data['layout_payload'], true) ?? []],
            [
                'layout' => ['array'],
                'layout.*.id' => ['required', 'integer'],
                'layout.*.pos_x' => ['required', 'numeric', 'min:0', 'max:100'],
                'layout.*.pos_y' => ['required', 'numeric', 'min:0', 'max:100'],
                'layout.*.width' => ['required', 'numeric', 'min:1', 'max:1000'],
                'layout.*.height' => ['required', 'numeric', 'min:1', 'max:1000'],
                'layout.*.rotation' => ['required', 'numeric', 'min:-360', 'max:360'],
            ],
        )->validate()['layout'];

        $saveTableLayout->handle(array_values($items));

        return redirect()->route('admin.tables.index', ['locale' => $locale])
            ->with('status', __('admin-tables-layout-updated'));
    }

    /**
     * @return Collection<int, Floor>
     */
    private function floorsWithRooms(): Collection
    {
        return Floor::query()->with('rooms')->orderBy('sort_order')->orderBy('id')->get();
    }
}
