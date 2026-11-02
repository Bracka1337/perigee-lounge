<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PromoCode;

class PromoCodeController extends Controller
{
    public function index(string $locale): View
    {
        $user = Auth::user();
        $promoCodes = $user->promoCodes()
            ->withPivot('claimed_at', 'used_at', 'is_used', 'is_redeemed', 'redeemed_at')
            ->with(['menuCategory:id,name', 'menuItem:id,name'])
            ->get();

        return view('client.promo_codes.index', [
            'promoCodes' => $promoCodes,
        ]);
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'public_code' => ['required', 'string'],
        ]);

        $user = Auth::user();

        $promo = PromoCode::where('public_code', $data['public_code'])->first();

        if ($promo === null) {
            return back()->with('promo_error', __('client-promo-not-found'));
        }

        if (!$promo->isClaimable()) {
            return back()->with('promo_error', __('client-promo-not-claimable'));
        }

        if ($promo->isClaimedBy($user)) {
            return back()->with('promo_error', __('client-promo-already-claimed'));
        }

        // When client adds promo to profile, treat it as claimed and consumed
        // (decrement available count). Actual redemption in an order is tracked
        // separately via pivot `is_redeemed`.
        $success = $promo->claimForUser($user, true);

        if (!$success) {
            return back()->with('promo_error', __('client-promo-not-claimable'));
        }

        return back()->with('promo_success', __('client-promo-claimed'));
    }
}
