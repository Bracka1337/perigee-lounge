<?php

namespace App\Actions\Orders;

use App\Exceptions\BusinessRuleException;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\User;
use App\Services\TableCart;
use App\Services\TelegramNotifier;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends a client's QR-table cart to the table's open staff order and notifies staff.
 * Staff must have opened the order first; a client can't add to another client's order.
 */
class ConfirmTableCart
{
    public function __construct(private TableCart $cart) {}

    public function handle(Table $table, ?User $user): Order
    {
        $items = $this->cart->items($table->id);
        if ($items === []) {
            throw new BusinessRuleException('cart-empty');
        }

        $orderable = MenuItem::with('category')->findMany(array_keys($items))
            ->filter(fn (MenuItem $item) => $item->isOrderable())
            ->keyBy('id');

        if ($orderable->count() !== count($items)) {
            $this->cart->put($table->id, array_intersect_key($items, $orderable->all()));
            throw new BusinessRuleException('menu-unavailable');
        }

        $order = Order::active()->where('table_id', $table->id)->first();
        if ($order === null) {
            throw new BusinessRuleException('table-no-open-order');
        }

        if ($user !== null && $order->user_id !== null && $order->user_id !== $user->id) {
            throw new BusinessRuleException('table-occupied');
        }

        if ($user !== null && $order->user_id === null) {
            $order->user_id = $user->id;
            $order->save();
        }

        // Each confirmation adds new lines, so staff see what arrived in this round.
        $newItemIds = [];
        foreach ($items as $menuItemId => $qty) {
            $price = $orderable->get($menuItemId)->price;
            $newItemIds[] = OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $menuItemId,
                'quantity' => (int) $qty,
                'unit_price' => $price,
                'total_price' => $price * (int) $qty,
            ])->id;
        }

        $order->refresh();
        $order->recalculateTotals();
        $order->source = $order->source ?: 'qr';
        $order->save();

        try {
            TelegramNotifier::notifyAddedItems($order, $newItemIds);
        } catch (Throwable $e) {
            Log::warning('Telegram notification failed: '.$e->getMessage());
        }

        $this->cart->clear($table->id);

        return $order;
    }
}
