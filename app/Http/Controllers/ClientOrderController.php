<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ClientOrderController extends Controller
{
    public function index(Request $request, string $locale, #[CurrentUser] User $user): View
    {
        return view('client.orders.index', [
            'orders' => $user->orders()
                ->with(['items.menuItem.category', 'table'])
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function show(string $locale, Order $order): View
    {
        Gate::authorize('view', $order);

        $order->load(['items.menuItem.category', 'table', 'promoCode']);
        // Ensure totals are up to date for display (no persistence here).
        $order->recalculateTotals();

        return view('client.orders.show', [
            'order' => $order,
            'loyaltySpent' => $order->loyaltySpent(),
        ]);
    }
}
