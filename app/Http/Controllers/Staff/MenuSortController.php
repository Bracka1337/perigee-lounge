<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuSortController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $categories = $request->validate([
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['array'],
            'categories.*.id' => ['nullable', 'integer'],
            'categories.*.items' => ['sometimes', 'array'],
            'categories.*.items.*' => ['integer'],
        ])['categories'] ?? [];

        foreach ($categories as $catIndex => $categoryData) {
            if (! isset($categoryData['id']) || $categoryData['id'] === '') {
                continue;
            }

            /** @var MenuCategory|null $category */
            $category = MenuCategory::find($categoryData['id']);

            if ($category === null) {
                continue;
            }

            $category->sort_order = $catIndex;
            $category->save();

            $items = $categoryData['items'] ?? [];

            foreach ($items as $itemIndex => $itemId) {
                /** @var MenuItem|null $item */
                $item = MenuItem::find($itemId);

                if ($item === null) {
                    continue;
                }

                $item->menu_category_id = $category->id;
                $item->sort_order = $itemIndex;
                $item->save();
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
