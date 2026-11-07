<?php

namespace App\Services;

use App\Models\MenuItem;
use Illuminate\Contracts\Session\Session;

/**
 * The QR-table cart a client fills before sending items to the waiter.
 * Kept in the session as [menu item id => quantity], one cart per table.
 */
class TableCart
{
    public function __construct(private Session $session) {}

    /**
     * @return array<int, int>
     */
    public function items(int $tableId): array
    {
        return $this->session->get($this->key($tableId), []);
    }

    /**
     * @param  array<int, int>  $items
     */
    public function put(int $tableId, array $items): void
    {
        $this->session->put($this->key($tableId), $items);
    }

    public function add(int $tableId, int $menuItemId, int $quantity): void
    {
        $items = $this->items($tableId);
        $items[$menuItemId] = ($items[$menuItemId] ?? 0) + $quantity;
        $this->put($tableId, $items);
    }

    public function setQuantity(int $tableId, int $menuItemId, int $quantity): void
    {
        $items = $this->items($tableId);

        if (array_key_exists($menuItemId, $items)) {
            $items[$menuItemId] = $quantity;
            $this->put($tableId, $items);
        }
    }

    public function remove(int $tableId, int $menuItemId): void
    {
        $items = $this->items($tableId);
        unset($items[$menuItemId]);
        $this->put($tableId, $items);
    }

    public function clear(int $tableId): void
    {
        $this->session->forget($this->key($tableId));
    }

    /**
     * Cart lines with prices. Drops items deleted from the menu since they were added.
     *
     * @return array{lines: array<int, array{item: MenuItem, qty: int, sum: float}>, total: float}
     */
    public function summary(int $tableId): array
    {
        $items = $this->items($tableId);
        $lines = [];
        $total = 0.0;

        if ($items === []) {
            return ['lines' => $lines, 'total' => $total];
        }

        $menuItems = MenuItem::whereIn('id', array_keys($items))->get()->keyBy('id');
        $items = array_intersect_key($items, $menuItems->all());
        $this->put($tableId, $items);

        foreach ($items as $menuItemId => $qty) {
            $menuItem = $menuItems->get($menuItemId);
            if ($menuItem === null) {
                continue; // Can't happen: deleted items were dropped above.
            }
            $sum = $menuItem->price * $qty;
            $total += $sum;
            $lines[$menuItemId] = ['item' => $menuItem, 'qty' => $qty, 'sum' => $sum];
        }

        return ['lines' => $lines, 'total' => $total];
    }

    private function key(int $tableId): string
    {
        return 'client_table_cart_'.$tableId;
    }
}
