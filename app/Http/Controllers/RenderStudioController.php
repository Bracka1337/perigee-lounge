<?php

namespace App\Http\Controllers;

use App\Http\Requests\RenderStudioRequest;
use App\Models\MenuItem;
use App\Models\Room;
use App\Models\Table;
use App\Services\LoungeLayout;
use Illuminate\Contracts\View\View;

/**
 * Draws one three.js scene from URL parameters, for the headless browser to photograph.
 * RenderStudioRequest decides who may open it.
 */
class RenderStudioController extends Controller
{
    public function __invoke(RenderStudioRequest $request, LoungeLayout $layout): View
    {
        app()->setLocale((string) config('site.default_locale', 'en'));

        $scene = $request->string('scene')->value();
        $id = $request->integer('id');

        $data = match ($scene) {
            'room' => ['scene' => 'room', 'focus' => Room::query()->findOrFail($id)->id, 'layout' => $layout->toArray($layout->floors(activeTablesOnly: false))],
            'table' => ['scene' => 'table', 'focus' => Table::query()->findOrFail($id)->id, 'layout' => $layout->toArray($layout->floors(activeTablesOnly: false))],
            default => ['scene' => 'menu', 'item' => $this->menuItem($id)],
        };

        return view('render-studio', ['data' => $data]);
    }

    /**
     * @return array{id: int, name: string, description: string, category: string}
     */
    private function menuItem(int $id): array
    {
        $item = MenuItem::query()->with('category')->findOrFail($id);

        return [
            'id' => $item->id,
            'name' => (string) ($item->name['en'] ?? $item->t('name')),
            'description' => (string) ($item->description['en'] ?? ''),
            'category' => (string) ($item->category->name['en'] ?? ''),
        ];
    }
}
