<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MenuCategoryController extends Controller
{
    public function index(): View
    {
        $categories = MenuCategory::with('items')->ordered()->get();

        return view('staff.menu.index', compact('categories'));
    }

    public function create(): View
    {
        return view('staff.menu.create-category');
    }

    public function store(Request $request): RedirectResponse
    {
        $locales = config('site.locales', ['ru']);
        $default = config('site.default_locale', $locales[0] ?? 'ru');

        $data = $request->validate([
            'name' => ['required', 'array'],
            "name.$default" => ['required', 'string', 'max:255'],
            'name.*' => ['nullable', 'string', 'max:255'],

            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string'],

            'is_special' => ['nullable', 'boolean'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $categoryData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? [],
            'is_special' => $request->boolean('is_special'),
            'is_available' => $request->has('is_available') ? $request->boolean('is_available') : true,
        ];

        $maxOrder = MenuCategory::max('sort_order') ?? 0;
        $categoryData['sort_order'] = $maxOrder + 1;

        MenuCategory::create($categoryData);

        return redirect()->route('staff.menu-categories.index')
            ->with('success', __('staff-menu-category-created'));
    }

    public function edit(string $locale, MenuCategory $menuCategory): View
    {
        return view('staff.menu.edit-category', compact('menuCategory'));
    }

    public function update(Request $request, string $locale, MenuCategory $menuCategory): RedirectResponse
    {
        $locales = config('site.locales', ['ru']);
        $default = config('site.default_locale', $locales[0] ?? 'ru');

        $data = $request->validate([
            'name' => ['required', 'array'],
            "name.$default" => ['required', 'string', 'max:255'],
            'name.*' => ['nullable', 'string', 'max:255'],

            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string'],

            'is_special' => ['nullable', 'boolean'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $categoryData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? [],
            'is_special' => $request->boolean('is_special'),
            'is_available' => $request->boolean('is_available'),
        ];

        $menuCategory->update($categoryData);

        return redirect()->route('staff.menu-categories.index')
            ->with('success', __('staff-menu-category-updated'));
    }

    public function destroy(string $locale, MenuCategory $menuCategory): RedirectResponse
    {
        if ($menuCategory->items()->whereHas('orderItems')->exists()) {
            return redirect()->route('staff.menu-categories.index')
                ->with('error', __('staff-menu-category-delete-blocked'));
        }

        $menuCategory->delete();

        return redirect()->route('staff.menu-categories.index')
            ->with('success', __('staff-menu-category-deleted'));
    }
}
