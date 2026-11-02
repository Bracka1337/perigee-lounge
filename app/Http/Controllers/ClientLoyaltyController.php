<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class ClientLoyaltyController extends Controller
{
    public function show(string $locale): View
    {
        $user = auth()->user();

        if ($user->loyalty_code === null || $user->loyalty_code === '') {
            $user->loyalty_code = Str::uuid()->toString();
            $user->save();
        }

        $qrData = $user->loyalty_code;

        $earnings = $user->loyaltyEarnings()
            ->with('order.table')
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        return view('client.loyalty.show', [
            'user' => $user,
            'qrData' => $qrData,
            'earnings' => $earnings,
        ]);
    }
}
