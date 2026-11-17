<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OpeningHour;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OpeningHourController extends Controller
{
    public function index(string $locale): View
    {
        return view('admin.opening-hours.index', [
            'hours' => OpeningHour::orderBy('weekday')->get()->keyBy('weekday'),
        ]);
    }

    public function update(Request $request, string $locale): RedirectResponse
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

        return redirect()->route('admin.opening-hours.index', ['locale' => $locale])
            ->with('status', __('admin-tables-opening-hours-updated'));
    }
}
