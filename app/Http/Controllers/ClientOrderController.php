<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\LoyaltyEarning;
use Illuminate\Contracts\View\View;

class ClientOrderController extends Controller
{
    public function index(string $locale): View
    {
        $orders = Order::with(['items.menuItem.category', 'table'])
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get();

        return view('client.orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(string $locale, Order $order): View
    {
        abort_unless($order->user_id === auth()->id(), 404);

        $order->load(['items.menuItem.category', 'table', 'promoCode']);
        // Ensure totals are up to date for display (no persistence here).
        $order->recalculateTotals();

        $loyaltySpent = LoyaltyEarning::where('order_id', $order->id)
            ->where('amount', '<', 0)
            ->sum('amount');
        $loyaltySpent = $loyaltySpent < 0 ? abs($loyaltySpent) : 0;

        return view('client.orders.show', [
            'order' => $order,
            'loyaltySpent' => $loyaltySpent,
        ]);
    }
}
