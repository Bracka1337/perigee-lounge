<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PromoCodeController extends Controller
{
    public function index(string $locale): View
    {
        $promoCodes = PromoCode::orderByDesc('created_at')->get();

        return view('staff.promo-codes.index', [
            'promoCodes' => $promoCodes,
        ]);
    }

    public function create(string $locale): View
    {
        $categories = MenuCategory::ordered()->get();
        $items = MenuItem::orderBy('menu_category_id')->orderBy('sort_order')->get();

        return view('staff.promo-codes.create', [
            'categories' => $categories,
            'items' => $items,
        ]);
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'public_code' => ['nullable', 'string', 'max:50', 'unique:promo_codes,public_code'],
            'description' => ['nullable', 'string'],
            'discount_type' => ['required', 'in:percent,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'scope' => ['required', 'in:all,category,item'],
            'menu_category_id' => ['nullable', 'exists:menu_categories,id'],
            'menu_item_id' => ['nullable', 'exists:menu_items,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($data['scope'] !== 'category') {
            $data['menu_category_id'] = null;
        }

        if ($data['scope'] !== 'item') {
            $data['menu_item_id'] = null;
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['used_count'] = 0;
        $data['claimed_count'] = 0;

        // ensure internal qr_code (UUID) is set and unique across promo_codes.qr_code and users.loyalty_code
        do {
            $internal = (string) Str::uuid();
            $existsInPromos = DB::table('promo_codes')->where('qr_code', $internal)->exists();
            $existsInUsers = DB::table('users')->where('loyalty_code', $internal)->exists();
            $collision = $existsInPromos || $existsInUsers;
        } while ($collision);

        // set internal qr_code to unique UUID
        $data['qr_code'] = $internal;

        // For backwards compatibility: if legacy `code` column still exists
        // in the database, populate it with the same internal UUID so that
        // DB constraints (not-null/unique) do not break creation until the
        // drop migration is applied.
        if (Schema::hasColumn('promo_codes', 'code')) {
            $data['code'] = $internal;
        }

        PromoCode::create($data);

        return redirect()->route('staff.promo-codes.index', ['locale' => $locale])
            ->with('success', __('staff-promo-created'));
    }

    public function edit(string $locale, PromoCode $promoCode): View
    {
        $categories = MenuCategory::ordered()->get();
        $items = MenuItem::orderBy('menu_category_id')->orderBy('sort_order')->get();

        return view('staff.promo-codes.edit', [
            'promoCode' => $promoCode,
            'categories' => $categories,
            'items' => $items,
        ]);
    }

    public function update(Request $request, string $locale, PromoCode $promoCode): RedirectResponse
    {
        $data = $request->validate([
            'public_code' => ['nullable', 'string', 'max:50', 'unique:promo_codes,public_code,' . $promoCode->id],
            'description' => ['nullable', 'string'],
            'discount_type' => ['required', 'in:percent,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'scope' => ['required', 'in:all,category,item'],
            'menu_category_id' => ['nullable', 'exists:menu_categories,id'],
            'menu_item_id' => ['nullable', 'exists:menu_items,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($data['scope'] !== 'category') {
            $data['menu_category_id'] = null;
        }

        if ($data['scope'] !== 'item') {
            $data['menu_item_id'] = null;
        }

        $data['is_active'] = $request->boolean('is_active');

        $promoCode->update($data);

        return redirect()->route('staff.promo-codes.index', ['locale' => $locale])
            ->with('success', __('staff-promo-updated'));
    }

    public function destroy(string $locale, PromoCode $promoCode): RedirectResponse
    {
        $promoCode->delete();

        return redirect()->route('staff.promo-codes.index', ['locale' => $locale])
            ->with('success', __('staff-promo-deleted'));
    }
}
