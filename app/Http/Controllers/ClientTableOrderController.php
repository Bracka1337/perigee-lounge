<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Services\TelegramNotifier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientTableOrderController extends Controller
{
    /**
     * Show the menu for a specific table QR.
     */
    public function show(string $locale, string $code): View|RedirectResponse
    {
        // If user is not authenticated, redirect to regular menu
        if (!auth()->check()) {
            return redirect()->route('menu', ['locale' => $locale]);
        }

        $table = $this->resolveTableFromCode($code);
        if ($table === null) {
            return redirect()->route('menu', ['locale' => $locale])
                ->with('error', 'Стол не найден');
        }

        // Check if user is already bound to another table
        $boundToAnother = $this->checkUserBoundToAnotherTable(auth()->id(), $table->id);
        if ($boundToAnother !== null) {
            return redirect()->route('menu', ['locale' => $locale])
                ->with('error', __('error-already-bound-to-table', ['table' => $boundToAnother]));
        }

        // Show standard menu page, with optional "Order for this table" button.
        $categories = MenuCategory::with(['items' => function ($q) {
            $q->orderBy('sort_order');
        }])->orderBy('sort_order')->get();

        return view('pages.menu', [
            'categories' => $categories,
            'qrTable' => $table,
            'qrCode' => $code,
        ]);
    }

    /**
     * Add an item to the session cart for the table.
     */
    public function addToCart(Request $request, string $locale, string $code): RedirectResponse
    {
        $table = $this->resolveTableFromCode($code);
        if ($table === null) {
            return redirect()->route('menu', ['locale' => $locale])
                ->with('error', 'Стол не найден');
        }

        if (!$table->is_active) {
            return redirect()->route('client.table.menu', ['locale' => $locale, 'code' => $code])
                ->with('error', 'Стол недоступен для заказа');
        }

        if (auth()->check()) {
            // Check if user is already bound to another table
            $boundToAnother = $this->checkUserBoundToAnotherTable(auth()->id(), $table->id);
            if ($boundToAnother !== null) {
                return redirect()->route('menu', ['locale' => $locale])
                    ->with('error', __('error-already-bound-to-table', ['table' => $boundToAnother]));
            }

            $order = Order::where('table_id', $table->id)
                ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
                ->first();

            if ($order !== null && $order->user_id !== null && $order->user_id !== auth()->id()) {
                return redirect()->route('client.table.menu', ['locale' => $locale, 'code' => $code])
                    ->with('error', 'Этот стол уже занят другим клиентом');
            }
        }

        $data = $request->validate([
            'menu_item_id' => ['required', 'exists:menu_items,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $qty = (int)($data['quantity'] ?? 1);
        $cartKey = $this->cartKey($table->id);
        $cart = session()->get($cartKey, []);
        $cart[$data['menu_item_id']] = ($cart[$data['menu_item_id']] ?? 0) + $qty;
        session()->put($cartKey, $cart);

        return back()->with('success', __('item-added-to-cart'));
    }

    /**
     * Show cart contents for table before confirming.
     */
    public function cart(string $locale, string $code): View|RedirectResponse
    {
        // If user is not authenticated, redirect to regular menu
        if (!auth()->check()) {
            return redirect()->route('menu', ['locale' => $locale]);
        }

        $table = $this->resolveTableFromCode($code);
        if ($table === null) {
            return redirect()->route('menu', ['locale' => $locale])
                ->with('error', 'Стол не найден');
        }

        $cartKey = $this->cartKey($table->id);
        $cart = session()->get($cartKey, []);

        $cartItems = [];
        $cartTotal = 0;
        if (!empty($cart)) {
            $items = MenuItem::whereIn('id', array_keys($cart))->get()->keyBy('id');
            foreach ($cart as $menuItemId => $qty) {
                $menuItem = $items->get((int)$menuItemId);
                $sum = ($menuItem?->price ?? 0) * (int)$qty;
                $cartTotal += $sum;
                $cartItems[(int)$menuItemId] = [
                    'item' => $menuItem,
                    'qty' => (int)$qty,
                    'sum' => $sum,
                ];
            }
        }

        $categories = MenuCategory::with(['items' => function ($q) {
            $q->orderBy('sort_order');
        }])->orderBy('sort_order')->get();

        return view('client.table.cart', [
            'table' => $table,
            'cartItems' => $cartItems,
            'cartTotal' => $cartTotal,
            'code' => $code,
            'categories' => $categories,
        ]);
    }

    /**
     * Confirm the cart and push items into the open staff order for this table.
     */
    public function confirm(Request $request, string $locale, string $code): RedirectResponse
    {
        $table = $this->resolveTableFromCode($code);
        if ($table === null) {
            return redirect()->route('menu', ['locale' => $locale])
                ->with('error', 'Стол не найден');
        }

        if (!$table->is_active) {
            return redirect()->route('client.table.cart.show', ['locale' => $locale, 'code' => $code])
                ->with('error', 'Стол недоступен для заказа');
        }

        // Check if user is already bound to another table
        if (auth()->check()) {
            $boundToAnother = $this->checkUserBoundToAnotherTable(auth()->id(), $table->id);
            if ($boundToAnother !== null) {
                return redirect()->route('menu', ['locale' => $locale])
                    ->with('error', __('error-already-bound-to-table', ['table' => $boundToAnother]));
            }
        }

        $cartKey = $this->cartKey($table->id);
        $cart = session()->get($cartKey, []);
        if (empty($cart)) {
            return redirect()->route('client.table.cart.show', ['locale' => $locale, 'code' => $code])
                ->with('error', 'Корзина пуста');
        }

        $order = Order::where('table_id', $table->id)
            ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->first();

        if ($order === null) {
            return redirect()->route('client.table.cart.show', ['locale' => $locale, 'code' => $code])
                ->with('error', 'Нет открытого заказа для этого стола');
        }

        // Allow both guest and authenticated users to add items
        // For guests: order.user_id remains null (or we can set it if auth check exists)
        // For authenticated: link order to current user
        if ($order->user_id !== null && auth()->check() && $order->user_id !== auth()->id()) {
            return redirect()->route('client.table.cart.show', ['locale' => $locale, 'code' => $code])
                ->with('error', 'Этот стол уже занят другим клиентом');
        }

        if (auth()->check() && $order->user_id === null) {
            $order->user_id = auth()->id();
            $order->save();
        }

        $newItemIds = [];
        foreach ($cart as $menuItemId => $qty) {
            $menuItem = MenuItem::find($menuItemId);
            if ($menuItem === null) {
                continue;
            }
            // Always create a new item record (don't sum with existing)
            $newItem = OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $menuItemId,
                'quantity' => (int)$qty,
                'unit_price' => $menuItem->price,
                'total_price' => $menuItem->price * (int)$qty,
            ]);
            $newItemIds[] = $newItem->id;
        }

        $order->refresh();
        $order->recalculateTotals();
        $order->source = $order->source ?: 'qr';
        $order->save();

        try {
            if (!empty($newItemIds)) {
                TelegramNotifier::notifyAddedItems($order, $newItemIds);
            }
        } catch (\Throwable $e) {
            \Log::warning('Telegram notification failed: '.$e->getMessage());
        }

        session()->forget($cartKey);

        // For both authenticated and guest users (auth is now required by middleware)
        // Redirect to order details if user is verified, otherwise to menu
        if (auth()->check() && auth()->user()->hasVerifiedEmail()) {
            return redirect()->route('client.orders.show', ['locale' => $locale, 'order' => $order])
                ->with('success', 'Заказ отправлен официанту');
        } else {
            return redirect()->route('client.table.menu', ['locale' => $locale, 'code' => $code])
                ->with('success', 'Заказ отправлен официанту');
        }
    }

    /**
     * Update quantity of item in the session cart for the table.
     */
    public function updateCart(Request $request, string $locale, string $code, int $itemId): RedirectResponse
    {
        $table = $this->resolveTableFromCode($code);
        if ($table === null) {
            return redirect()->route('menu', ['locale' => $locale])
                ->with('error', 'Стол не найден');
        }

        $cartKey = $this->cartKey($table->id);
        $cart = session()->get($cartKey, []);

        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $qty = (int)$data['qty'];
        if ($qty > 0) {
            $cart[$itemId] = $qty;
        } else {
            unset($cart[$itemId]);
        }

        session()->put($cartKey, $cart);

        return redirect()->route('client.table.cart.show', ['locale' => $locale, 'code' => $code]);
    }

    /**
     * Remove an item from the session cart for the table.
     */
    public function removeFromCart(string $locale, string $code, int $itemId): RedirectResponse
    {
        $table = $this->resolveTableFromCode($code);
        if ($table === null) {
            return redirect()->route('menu', ['locale' => $locale])
                ->with('error', 'Стол не найден');
        }

        $cartKey = $this->cartKey($table->id);
        $cart = session()->get($cartKey, []);

        unset($cart[$itemId]);
        session()->put($cartKey, $cart);

        return redirect()->route('client.table.cart.show', ['locale' => $locale, 'code' => $code]);
    }

    /**
     * Show client catalog identical to staff to add items directly into the open order.
     */
    public function orderCatalog(string $locale, string $code): View|RedirectResponse
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $table = $this->resolveTableFromCode($code);
        if ($table === null) {
            return redirect()->route('menu', ['locale' => $locale])
                ->with('error', 'Стол не найден');
        }

        if (!$table->is_active) {
            return redirect()->route('menu', ['locale' => $locale])
                ->with('error', 'Стол недоступен для заказа');
        }

        // Check if user is already bound to another table
        $boundToAnother = $this->checkUserBoundToAnotherTable(auth()->id(), $table->id);
        if ($boundToAnother !== null) {
            return redirect()->route('menu', ['locale' => $locale])
                ->with('error', __('error-already-bound-to-table', ['table' => $boundToAnother]));
        }

        $order = Order::where('table_id', $table->id)
            ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->first();

        if ($order === null) {
            return redirect()->route('client.table.menu', ['locale' => $locale, 'code' => $code])
                ->with('error', 'Нет открытого заказа для этого стола');
        }

        if ($order->user_id === null) {
            $order->user_id = auth()->id();
            $order->save();
        }

        $categories = MenuCategory::with('items')->ordered()->get();

        return view('client.table.catalog', [
            'order' => $order,
            'categories' => $categories,
            'code' => $code,
        ]);
    }

    /**
     * Add item to order from client catalog.
     */
    public function orderAddItem(Request $request, string $locale, string $code): RedirectResponse
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $table = $this->resolveTableFromCode($code);
        if ($table === null || !$table->is_active) {
            return redirect()->route('menu', ['locale' => $locale])
                ->with('error', 'Стол недоступен для заказа');
        }

        // Check if user is already bound to another table
        $boundToAnother = $this->checkUserBoundToAnotherTable(auth()->id(), $table->id);
        if ($boundToAnother !== null) {
            return redirect()->route('menu', ['locale' => $locale])
                ->with('error', __('error-already-bound-to-table', ['table' => $boundToAnother]));
        }

        $order = Order::where('table_id', $table->id)
            ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->firstOrFail();

        if ($order->status !== Order::STATUS_OPEN) {
            return back()->with('error', 'Заказ закрыт для изменений');
        }

        $data = $request->validate([
            'menu_item_id' => ['required', 'exists:menu_items,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $qty = (int)($data['quantity'] ?? 1);

        $existing = $order->items()->where('menu_item_id', $data['menu_item_id'])->first();
        if ($existing !== null) {
            $existing->quantity += $qty;
            $existing->total_price = $existing->unit_price * $existing->quantity;
            $existing->save();
        } else {
            $item = MenuItem::findOrFail($data['menu_item_id']);
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $item->id,
                'quantity' => $qty,
                'unit_price' => $item->price,
                'total_price' => $item->price * $qty,
            ]);
        }

        $order->recalculateTotals();
        $order->source = $order->source ?: 'qr';
        $order->save();

        try {
            TelegramNotifier::notifyNewOrder($order);
        } catch (\Throwable $e) {
            \Log::warning('Telegram notifyNewOrder failed: '.$e->getMessage());
        }

        return redirect()->route('client.table.order.catalog', ['locale' => $locale, 'code' => $code])
            ->with('success', 'Позиция добавлена');
    }

    protected function resolveTableFromCode(string $code): ?Table
    {
        // First, try match by table name (case-insensitive), e.g. "z4" → "Z4"
        $byName = Table::whereRaw('LOWER(name) = ?', [strtolower($code)])->first();
        if ($byName !== null) {
            return $byName;
        }

        // Fallback: support numeric id (e.g. "4") or prefixed like "z4" meaning id=4
        $normalized = $code;
        if (strlen($normalized) > 1 && strtolower($normalized[0]) === 'z') {
            $normalized = substr($normalized, 1) ?: '';
        }
        if (ctype_digit($normalized)) {
            return Table::find((int) $normalized);
        }
        return null;
    }

    protected function cartKey(int $tableId): string
    {
        return 'client_table_cart_'.$tableId;
    }

    /**
     * Check if the user is already bound to another table via an active order.
     * Returns the table name if bound to another table, null otherwise.
     */
    protected function checkUserBoundToAnotherTable(int $userId, int $requestedTableId): ?string
    {
        $existingOrder = Order::where('user_id', $userId)
            ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->first();

        if ($existingOrder !== null && $existingOrder->table_id !== $requestedTableId) {
            $boundTable = $existingOrder->table;
            return $boundTable?->name ?? "#{$existingOrder->table_id}";
        }

        return null;
    }
}
