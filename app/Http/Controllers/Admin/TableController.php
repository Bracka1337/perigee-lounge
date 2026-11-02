<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OpeningHour;
use App\Models\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TableController extends Controller
{
    public function index(string $locale): View
    {
        $tables = Table::orderBy('id')->get();

        $hours = OpeningHour::orderBy('weekday')->get()->keyBy('weekday');

        return view('admin.tables.index', [
            'tables' => $tables,
            'hours' => $hours,
        ]);
    }

    public function create(string $locale): View
    {
        return view('admin.tables.create');
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('tables', 'name')],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'pos_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pos_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'width' => ['nullable', 'integer', 'min:10', 'max:1000'],
            'height' => ['nullable', 'integer', 'min:10', 'max:1000'],
            'rotation' => ['nullable', 'integer', 'min:0', 'max:359'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Table::create([
            'name' => $data['name'],
            'capacity' => $data['capacity'],
            'pos_x' => $data['pos_x'] ?? 10,
            'pos_y' => $data['pos_y'] ?? 10,
            'width' => $data['width'] ?? 80,
            'height' => $data['height'] ?? 80,
            'rotation' => $data['rotation'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.tables.index', ['locale' => $locale])
            ->with('status', __('admin-tables-created'));
    }

    public function edit(string $locale, Table $table): View
    {
        return view('admin.tables.edit', [
            'table' => $table,
        ]);
    }

    public function update(Request $request, string $locale, Table $table): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('tables', 'name')->ignore($table->id)],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'pos_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pos_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'width' => ['nullable', 'integer', 'min:10', 'max:1000'],
            'height' => ['nullable', 'integer', 'min:10', 'max:1000'],
            'rotation' => ['nullable', 'integer', 'min:0', 'max:359'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $table->update([
            'name' => $data['name'],
            'capacity' => $data['capacity'],
            'pos_x' => $data['pos_x'] ?? $table->pos_x,
            'pos_y' => $data['pos_y'] ?? $table->pos_y,
            'width' => $data['width'] ?? $table->width,
            'height' => $data['height'] ?? $table->height,
            'rotation' => $data['rotation'] ?? $table->rotation,
            'is_active' => $request->boolean('is_active'),
        ]);

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
            'hours' => ['required', 'array'],
            'hours.*.is_open' => ['nullable', 'boolean'],
            'hours.*.opens_at' => ['nullable', 'date_format:H:i'],
            'hours.*.closes_at' => ['nullable', 'date_format:H:i'],
        ]);

        foreach ($data['hours'] as $weekday => $row) {
            $model = OpeningHour::firstOrNew(['weekday' => (int) $weekday]);
            $model->is_open = isset($row['is_open']) ? (bool) $row['is_open'] : false;
            $model->opens_at = $row['opens_at'] ? $row['opens_at'].':00' : null;
            $model->closes_at = $row['closes_at'] ? $row['closes_at'].':00' : null;
            $model->save();
        }

        return redirect()->route('admin.tables.index', ['locale' => $locale])
            ->with('status', __('admin-tables-opening-hours-updated'));
    }

    public function updateLayout(Request $request, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'layout_payload' => ['required', 'string'],
        ]);

        $items = json_decode($data['layout_payload'], true) ?? [];

        foreach ($items as $row) {
            if (! isset($row['id'])) {
                continue;
            }

            $table = Table::find($row['id']);
            if ($table === null) {
                continue;
            }

            $table->update([
                'pos_x' => $row['pos_x'],
                'pos_y' => $row['pos_y'],
                'width' => $row['width'],
                'height' => $row['height'],
                'rotation' => $row['rotation'],
            ]);
        }

        return redirect()->route('admin.tables.index', ['locale' => $locale])
            ->with('status', __('admin-tables-layout-updated'));
    }
}
