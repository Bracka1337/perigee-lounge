<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderItemCatalogController extends Controller
{
    public function index(string $locale, Order $order): View
    {
        if ($order->status !== 'open') {
            abort(403);
        }

        $categories = MenuCategory::with('items')->ordered()->get();

        return view('staff.orders.catalog', [
            'order' => $order,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request, string $locale, Order $order): RedirectResponse
    {
        if ($order->status !== 'open') {
            abort(403);
        }

        $data = $request->validate([
            'menu_item_id' => ['required', 'exists:menu_items,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $qty = $data['quantity'] ?? 1;

        $orderItem = OrderItem::firstOrNew([
            'order_id' => $order->id,
            'menu_item_id' => $data['menu_item_id'],
        ]);

        $orderItem->unit_price ??= $orderItem->menuItem->price;
        $orderItem->quantity += $qty;
        $orderItem->total_price = $orderItem->unit_price * $orderItem->quantity;
        $orderItem->save();

        $order->recalculateTotals();
        $order->save();

        return redirect()
            ->route('staff.orders.show', $order)
            ->with('success', 'Позиция добавлена в заказ');
    }
}