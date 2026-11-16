<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\MenuItemRequest;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Services\MenuPhotoStore;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class MenuItemController extends Controller
{
    public function __construct(private MenuPhotoStore $photos) {}

    public function create(Request $request): View
    {
        $categoryId = $request->input('category_id');
        $category = MenuCategory::findOrFail($categoryId);

        return view('staff.menu.create-item', compact('category'));
    }

    public function store(MenuItemRequest $request): RedirectResponse
    {
        $itemData = $request->itemData();
        $itemData['is_available'] = $request->has('is_available') ? $request->boolean('is_available') : true;

        $photo = $request->file('image');
        if ($photo instanceof UploadedFile) {
            $itemData = [...$itemData, ...$this->photos->store($photo), 'photo_generated' => false];
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

    public function update(MenuItemRequest $request, string $locale, MenuItem $menuItem): RedirectResponse
    {
        $itemData = $request->itemData();
        $itemData['is_available'] = $request->boolean('is_available');

        $photo = $request->file('image');
        $replacesPhoto = $photo instanceof UploadedFile || $request->boolean('remove_image');

        $oldPaths = [$menuItem->image_path, $menuItem->thumb_path];

        if ($replacesPhoto) {
            $itemData = [...$itemData, ...($photo instanceof UploadedFile
                ? $this->photos->store($photo)
                : ['image_path' => null, 'thumb_path' => null]), 'photo_generated' => false];
        }

        $menuItem->update($itemData);

        // Old files go only after the new paths are saved, so a failed update never leaves a broken image.
        if ($replacesPhoto) {
            $this->photos->delete(...$oldPaths);
        }

        return redirect()->route('staff.menu-categories.index')
            ->with('success', __('staff-menu-item-updated'));
    }

    public function destroy(string $locale, MenuItem $menuItem): RedirectResponse
    {
        if ($menuItem->orderItems()->exists()) {
            return redirect()->route('staff.menu-categories.index')
                ->with('error', __('staff-menu-item-delete-blocked'));
        }

        $menuItem->delete();
        $this->photos->delete($menuItem->image_path, $menuItem->thumb_path);

        return redirect()->route('staff.menu-categories.index')
            ->with('success', __('staff-menu-item-deleted'));
    }
}
