<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreOrderItemRequest;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class OrderItemCatalogController extends Controller
{
    public function index(string $locale, Order $order): View
    {
        Gate::authorize('addItems', $order);

        return view('staff.orders.catalog', [
            'order' => $order,
            'categories' => MenuCategory::with('items')->ordered()->get(),
        ]);
    }

    public function store(StoreOrderItemRequest $request, string $locale, Order $order): RedirectResponse
    {
        Gate::authorize('addItems', $order);

        $menuItem = MenuItem::with('category')->findOrFail($request->validated('menu_item_id'));

        if (! $menuItem->isOrderable()) {
            return back()->with('error', __('menu-unavailable'));
        }

        $orderItem = OrderItem::firstOrNew([
            'order_id' => $order->id,
            'menu_item_id' => $menuItem->id,
        ]);

        $quantity = (int) $orderItem->quantity + (int) ($request->validated('quantity') ?? 1);

        // The line total is capped too, so repeated adds can't push it past the limit.
        Validator::make(
            ['quantity' => $quantity],
            ['quantity' => ['integer', 'max:'.StoreOrderItemRequest::MAX_QUANTITY]],
        )->validate();

        $orderItem->unit_price ??= $menuItem->price;
        $orderItem->quantity = $quantity;
        $orderItem->total_price = $orderItem->unit_price * $quantity;
        $orderItem->save();

        $order->recalculateTotals();
        $order->save();

        return redirect()
            ->route('staff.orders.show', $order)
            ->with('success', __('staff-order-item-added'));
    }
}
