<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuItemController extends Controller
{
    public function create(Request $request): View
    {
        $categoryId = $request->get('category_id');
        $category   = MenuCategory::findOrFail($categoryId);

        return view('staff.menu.create-item', compact('category'));
    }

    public function store(Request $request): RedirectResponse
    {
        $locales = config('site.locales', ['ru']);
        $default = config('site.default_locale', $locales[0] ?? 'ru');

        $data = $request->validate([
            'menu_category_id'       => ['required', 'exists:menu_categories,id'],

            'name'                   => ['required', 'array'],
            "name.$default"          => ['required', 'string', 'max:255'],
            'name.*'                 => ['nullable', 'string', 'max:255'],

            'description'            => ['nullable', 'array'],
            'description.*'          => ['nullable', 'string'],

            'allergens'              => ['nullable', 'array'],
            'allergens.*'            => ['nullable', 'string'],

            'price'                  => ['required', 'numeric', 'min:0'],
            'image'                  => ['nullable', 'image', 'max:2048'],
            'is_available'           => ['nullable', 'boolean'],
        ]);

        $itemData = [
            'menu_category_id' => $data['menu_category_id'],
            'name'             => $data['name'],
            'description'      => $data['description'] ?? [],
            'allergens'        => $data['allergens'] ?? [],
            'price'            => $data['price'],
            'is_available'     => $request->has('is_available') ? $request->boolean('is_available') : true,
        ];

        if ($request->hasFile('image')) {
            $itemData['image_path'] = $request->file('image')->store('menu', 'public');
        }

        $maxOrder = MenuItem::where('menu_category_id', $itemData['menu_category_id'])->max('sort_order') ?? 0;
        $itemData['sort_order'] = $maxOrder + 1;

        MenuItem::create($itemData);

        return redirect()->route('staff.menu-categories.index')
            ->with('success', __('staff-menu-item-created'));
    }

    public function edit(string $locale, MenuItem $menuItem): View
    {
        $categories = MenuCategory::ordered()->get();

        return view('staff.menu.edit-item', compact('menuItem', 'categories'));
    }

    public function update(Request $request, string $locale, MenuItem $menuItem): RedirectResponse
    {
        $locales = config('site.locales', ['ru']);
        $default = config('site.default_locale', $locales[0] ?? 'ru');

        $data = $request->validate([
            'menu_category_id'       => ['required', 'exists:menu_categories,id'],

            'name'                   => ['required', 'array'],
            "name.$default"          => ['required', 'string', 'max:255'],
            'name.*'                 => ['nullable', 'string', 'max:255'],

            'description'            => ['nullable', 'array'],
            'description.*'          => ['nullable', 'string'],

            'allergens'              => ['nullable', 'array'],
            'allergens.*'            => ['nullable', 'string'],

            'price'                  => ['required', 'numeric', 'min:0'],
            'image'                  => ['nullable', 'image', 'max:2048'],
            'is_available'           => ['nullable', 'boolean'],
        ]);

        $itemData = [
            'menu_category_id' => $data['menu_category_id'],
            'name'             => $data['name'],
            'description'      => $data['description'] ?? [],
            'allergens'        => $data['allergens'] ?? [],
            'price'            => $data['price'],
            'is_available'     => $request->boolean('is_available'),
        ];

        if ($request->hasFile('image')) {
            if ($menuItem->image_path !== null && $menuItem->image_path !== '') {
                Storage::disk('public')->delete($menuItem->image_path);
            }
            $itemData['image_path'] = $request->file('image')->store('menu', 'public');
        }

        $menuItem->update($itemData);

        return redirect()->route('staff.menu-categories.index')
            ->with('success', __('staff-menu-item-updated'));
    }

    public function destroy(string $locale, MenuItem $menuItem): RedirectResponse
    {
        if ($menuItem->image_path !== null && $menuItem->image_path !== '') {
            Storage::disk('public')->delete($menuItem->image_path);
        }

        $menuItem->delete();

        return redirect()->route('staff.menu-categories.index')
            ->with('success', __('staff-menu-item-deleted'));
    }
}
