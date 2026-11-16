<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UpdateAvailabilityRequest;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;

/**
 * One-click available/unavailable switches on the staff menu list. The form sends the
 * target state rather than "toggle", so a double click can't flip it back.
 */
class MenuAvailabilityController extends Controller
{
    public function item(UpdateAvailabilityRequest $request, string $locale, MenuItem $menuItem): RedirectResponse
    {
        $menuItem->update(['is_available' => $request->boolean('is_available')]);

        return $this->backToCategory($menuItem->menu_category_id);
    }

    public function category(UpdateAvailabilityRequest $request, string $locale, MenuCategory $menuCategory): RedirectResponse
    {
        $menuCategory->update(['is_available' => $request->boolean('is_available')]);

        return $this->backToCategory($menuCategory->id);
    }

    private function backToCategory(int $categoryId): RedirectResponse
    {
        return redirect()->to(route('staff.menu-categories.index').'#staff-category-'.$categoryId)
            ->with('success', __('staff-menu-availability-updated'));
    }
}
