<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    public function index(string $locale, #[CurrentUser] User $user): View
    {
        $promoCodes = $user->promoCodes()
            ->withPivot('claimed_at', 'used_at', 'is_used', 'is_redeemed', 'redeemed_at')
            ->with(['menuCategory:id,name', 'menuItem:id,name'])
            ->get();

        return view('client.promo_codes.index', [
            'promoCodes' => $promoCodes,
        ]);
    }

    public function store(Request $request, string $locale, #[CurrentUser] User $user): RedirectResponse
    {
        $data = $request->validate([
            'public_code' => ['required', 'string'],
        ]);

        $promo = PromoCode::where('public_code', $data['public_code'])->first();

        if ($promo === null) {
            return back()->with('promo_error', __('client-promo-not-found'));
        }

        if (! $promo->isClaimable()) {
            return back()->with('promo_error', __('client-promo-not-claimable'));
        }

        if ($promo->isClaimedBy($user)) {
            return back()->with('promo_error', __('client-promo-already-claimed'));
        }

        // When client adds promo to profile, treat it as claimed and consumed
        // (decrement available count). Actual redemption in an order is tracked
        // separately via pivot `is_redeemed`.
        $success = $promo->claimForUser($user, true);

        if (! $success) {
            return back()->with('promo_error', __('client-promo-not-claimable'));
        }

        return back()->with('promo_success', __('client-promo-claimed'));
    }
}
