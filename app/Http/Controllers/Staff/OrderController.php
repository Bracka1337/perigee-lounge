<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyEarning;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use App\Models\Reservation;
use App\Models\Table;
use App\Models\User;
use App\Notifications\OrderCompleted;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request, string $locale): View
    {
        $date = $request->query('date');
        if ($date === null || $date === '' || (is_array($date) && count($date) === 0)) {
            $date = Carbon::now()->toDateString();
        }

        $selectedDate = Carbon::parse($date)->startOfDay();

        $tables = Table::where('is_active', true)->orderBy('id')->get();

        // Active orders - show ALL active orders regardless of date
        $ordersActive = Order::with(['table', 'user'])
            ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->orderByDesc('created_at')
            ->get();

        // History - only completed/cancelled orders for the selected date
        $ordersHistory = Order::with(['table', 'user'])
            ->whereDate('created_at', $selectedDate->toDateString())
            ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->orderByDesc('created_at')
            ->get();

        $reservations = Reservation::whereDate('start_at', $selectedDate->toDateString())
            ->whereIn('status', [Reservation::STATUS_PENDING, Reservation::STATUS_ACTIVE])
            ->whereDoesntHave('order')
            ->with(['table', 'user'])
            ->orderBy('start_at')
            ->get();

        return view('staff.orders.index', [
            'date' => $selectedDate,
            'tables' => $tables,
            'ordersActive' => $ordersActive,
            'ordersHistory' => $ordersHistory,
            'reservations' => $reservations,
        ]);
    }

    public function create(string $locale): View
    {
        $tables = Table::where('is_active', true)->orderBy('id')->get();
        $menuCategories = MenuCategory::with('items')->ordered()->get();

        // Получаем зарезервированные столы на сегодня
        $reservedTableIds = Reservation::whereDate('start_at', Carbon::now()->toDateString())
            ->whereIn('status', [Reservation::STATUS_PENDING, Reservation::STATUS_ACTIVE])
            ->pluck('table_id')
            ->toArray();

        // Получаем столы с открытыми заказами
        $openOrderTableIds = Order::whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->pluck('table_id')
            ->toArray();

        return view('staff.orders.create', [
            'tables' => $tables,
            'menuCategories' => $menuCategories,
            'reservedTableIds' => $reservedTableIds,
            'openOrderTableIds' => $openOrderTableIds,
        ]);
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'table_id' => ['required', 'exists:tables,id'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        $table = Table::where('is_active', true)->findOrFail($data['table_id']);

        if ($table->is_occupied) {
            return back()->withInput()->with('error', __('staff-orders-error-table-occupied'));
        }

        $hasActiveOrder = Order::where('table_id', $table->id)
            ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->exists();

        if ($hasActiveOrder) {
            return back()->withInput()->with('error', __('staff-orders-error-table-has-open-order'));
        }

        $now = Carbon::now();

        $todayReservation = Reservation::where('table_id', $table->id)
            ->whereDate('start_at', $now->toDateString())
            ->whereIn('status', [Reservation::STATUS_PENDING, Reservation::STATUS_ACTIVE])
            ->orderBy('start_at')
            ->first();

        if ($todayReservation !== null && $now->lt($todayReservation->start_at)) {
            return back()->withInput()->with('error', __('staff-orders-error-table-reserved'));
        }

        if ($todayReservation !== null && $todayReservation->status === Reservation::STATUS_PENDING) {
            $todayReservation->status = Reservation::STATUS_ACTIVE;
            $todayReservation->save();
        }

        try {
            $order = Order::create([
                'reservation_id' => $todayReservation?->id,
                'table_id' => $table->id,
                'user_id' => $data['user_id'] ?? $todayReservation?->user_id,
                'status' => Order::STATUS_OPEN,
                'started_at' => $now,
                'source' => ($todayReservation?->source !== null && $todayReservation?->source !== '') ? $todayReservation->source : 'manual',
            ]);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $table->is_occupied = true;
        $table->save();

        return redirect()->route('staff.orders.show', ['locale' => $locale, 'order' => $order]);
    }

    public function fromReservation(string $locale, Reservation $reservation): RedirectResponse
    {
        if ($reservation->order !== null) {
            return redirect()
                ->route('staff.orders.show', ['locale' => $locale, 'order' => $reservation->order])
                ->with('success', __('staff-orders-order-already-created'));
        }

        $now = Carbon::now();

        if ($now->lt($reservation->start_at)) {
            return redirect()
                ->route('staff.orders.index', [
                    'locale' => $locale,
                    'date' => $reservation->start_at->toDateString(),
                ])
                ->with('error', __('staff-orders-error-table-reserved'));
        }

        if ($reservation->status === Reservation::STATUS_PENDING) {
            $reservation->status = Reservation::STATUS_ACTIVE;
            $reservation->save();
        }

        $existingOrder = Order::where('table_id', $reservation->table_id)
            ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->first();

        if ($existingOrder !== null) {
            if (is_null($existingOrder->reservation_id)) {
                $existingOrder->reservation_id = $reservation->id;
                if ($existingOrder->source === null || $existingOrder->source === '') {
                    $existingOrder->source = ($reservation->source !== null && $reservation->source !== '') ? $reservation->source : 'reservation';
                }
                if (is_null($existingOrder->user_id) && ! is_null($reservation->user_id)) {
                    $existingOrder->user_id = $reservation->user_id;
                }
                $existingOrder->save();
                $order = $existingOrder;
            } else {
                return redirect()
                    ->route('staff.orders.index', [
                        'locale' => $locale,
                        'date' => $reservation->start_at->toDateString(),
                    ])
                    ->with('error', __('staff-orders-error-table-has-open-order'));
            }
        } else {
            $order = Order::create([
                'reservation_id' => $reservation->id,
                'table_id' => $reservation->table_id,
                'user_id' => $reservation->user_id,
                'status' => Order::STATUS_OPEN,
                'started_at' => $now,
                'source' => ($reservation->source !== null && $reservation->source !== '') ? $reservation->source : 'reservation',
            ]);
        }

        if ($reservation->table !== null) {
            $reservation->table->is_occupied = true;
            $reservation->table->save();
        }

        return redirect()
            ->route('staff.orders.show', ['locale' => $locale, 'order' => $order])
            ->with('success', __('staff-orders-order-created'));
    }

    public function show(string $locale, Order $order): View
    {
        $order->load(['items.menuItem.category', 'table', 'user', 'promoCode']);
        $menuCategories = MenuCategory::with('items')->ordered()->get();

        // Calculate loyalty spent (negative LoyaltyEarning entries)
        $loyaltySpent = LoyaltyEarning::where('order_id', $order->id)
            ->where('amount', '<', 0)
            ->sum('amount');
        $loyaltySpent = $loyaltySpent < 0 ? abs($loyaltySpent) : 0;

        return view('staff.orders.show', [
            'order' => $order,
            'menuCategories' => $menuCategories,
            'loyaltySpent' => $loyaltySpent,
        ]);
    }

    public function addItem(Request $request, string $locale, Order $order): RedirectResponse
    {
        if ($order->status !== Order::STATUS_OPEN) {
            return back()->with('error', __('staff-orders-add-item-forbidden'));
        }

        $data = $request->validate([
            'menu_item_id' => ['required', 'exists:menu_items,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $item = MenuItem::findOrFail($data['menu_item_id']);
        $quantity = $data['quantity'];
        $unitPrice = $item->price;
        $totalPrice = $unitPrice * $quantity;

        $existing = $order->items()->where('menu_item_id', $item->id)->first();
        if ($existing !== null) {
            $existing->quantity += $quantity;
            $existing->total_price = $existing->unit_price * $existing->quantity;
            $existing->save();
        } else {
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $item->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ]);
        }

        $order->refresh();
        $order->recalculateTotals();
        $order->save();

        return back()->with('success', __('staff-orders-item-added'));
    }

    public function updateItem(Request $request, string $locale, Order $order, OrderItem $item): RedirectResponse
    {
        if ($order->id !== $item->order_id) {
            abort(404);
        }

        if ($order->status !== Order::STATUS_OPEN) {
            return back()->with('error', __('staff-orders-update-item-forbidden'));
        }

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $item->quantity = $data['quantity'];
        $item->total_price = $item->unit_price * $item->quantity;
        $item->save();

        $order->refresh();
        $order->recalculateTotals();
        $order->save();

        return back()->with('success', __('staff-orders-item-updated'));
    }

    public function destroyItem(string $locale, Order $order, OrderItem $item): RedirectResponse
    {
        if ($order->id !== $item->order_id) {
            abort(404);
        }

        if ($order->status !== Order::STATUS_OPEN) {
            return back()->with('error', __('staff-orders-delete-item-forbidden'));
        }

        $item->delete();

        $order->refresh();
        $order->recalculateTotals();
        $order->save();

        return back()->with('success', __('staff-orders-item-deleted'));
    }

    public function attachClient(Request $request, string $locale, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'loyalty_code' => ['required', 'string'],
        ]);

        $user = User::where('loyalty_code', $data['loyalty_code'])->first();

        if ($user === null) {
            return back()->with('error', __('staff-orders-attach-client-not-found'));
        }

        $order->user_id = $user->id;
        $order->save();

        return back()->with('success', __('staff-orders-attach-client-success'));
    }

    public function applyPromo(Request $request, string $locale, Order $order): RedirectResponse
    {
        if ($order->status !== Order::STATUS_OPEN) {
            return back()->with('error', __('staff-orders-apply-promo-forbidden'));
        }

        $data = $request->validate([
            'code' => ['required', 'string'],
        ]);

        if ($order->promo_code_id !== null) {
            return back()->with('error', __('staff-orders-promo-already-applied'));
        }

        // Поиск промокода по public_code или qr_code
        $promo = PromoCode::where('public_code', $data['code'])
            ->orWhere('qr_code', $data['code'])
            ->first();

        if ($promo === null || ! $promo->isAvailable()) {
            return back()->with('error', __('staff-orders-promo-invalid'));
        }

        $order->loadMissing('items.menuItem');

        $discount = $promo->calculateDiscountForOrder($order);
        if ($discount <= 0) {
            return back()->with('error', __('staff-orders-promo-no-eligible-items'));
        }

        DB::transaction(function () use ($order, $promo, $discount) {
            $order->promo_code_id = $promo->id;
            $order->recalculateTotals();
            $order->save();

            PromoCodeUsage::create([
                'promo_code_id' => $promo->id,
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'discount_amount' => $discount,
            ]);

            $promo->used_count++;
            $promo->save();
        });

        return back()->with('success', __('staff-orders-promo-applied'));
    }

    public function applyLoyalty(Request $request, string $locale, Order $order): RedirectResponse
    {
        if ($order->status !== Order::STATUS_OPEN) {
            return back()->with('error', __('staff-orders-apply-loyalty-forbidden'));
        }

        if ($order->user === null) {
            return back()->with('error', __('staff-orders-apply-loyalty-no-client'));
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $amount = (float) $data['amount'];
        $maxAllowed = min($order->user->loyalty_balance ?? 0, $order->total ?? 0);

        if ($amount > $maxAllowed) {
            return back()->with('error', __('staff-orders-apply-loyalty-exceeds-limit'));
        }

        if ($amount <= 0) {
            return back()->with('error', __('staff-orders-apply-loyalty-invalid-amount'));
        }

        DB::transaction(function () use ($order, $amount) {
            // Deduct loyalty balance from user
            $order->user->loyalty_balance = max(0, ($order->user->loyalty_balance ?? 0) - $amount);
            $order->user->save();

            // Record the loyalty spending as a negative LoyaltyEarning entry
            LoyaltyEarning::create([
                'user_id' => $order->user->id,
                'order_id' => $order->id,
                'amount' => -$amount, // Negative amount indicates spending
            ]);

            // Recalculate order totals to include loyalty discount
            $order->recalculateTotals();
            $order->save();
        });

        return back()->with('success', __('staff-orders-apply-loyalty-applied'));
    }

    public function complete(string $locale, Order $order): RedirectResponse
    {
        if ($order->status !== Order::STATUS_OPEN) {
            return back()->with('error', __('staff-orders-complete-forbidden'));
        }

        DB::transaction(function () use ($order) {
            $order->loadMissing('items', 'reservation', 'user');
            $order->recalculateTotals();
            $order->status = Order::STATUS_COMPLETED;
            $order->completed_at = Carbon::now();
            $order->save();

            if ($order->reservation !== null && in_array($order->reservation->status, [Reservation::STATUS_PENDING, Reservation::STATUS_ACTIVE], true)) {
                $order->reservation->status = Reservation::STATUS_COMPLETED;
                $order->reservation->end_at = Carbon::now();
                $order->reservation->save();
            }

            if ($order->user !== null && $order->total > 0) {
                $bonus = round($order->total * 0.01, 2);
                if ($bonus > 0) {
                    $user = $order->user;
                    $user->loyalty_balance = ($user->loyalty_balance ?? 0) + $bonus;
                    $user->save();

                    LoyaltyEarning::create([
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'amount' => $bonus,
                    ]);
                }
            }
        });

        if ($order->user !== null) {
            $order->user->notify(new OrderCompleted($order));
        }

        $this->releaseTableIfFree($order);

        return redirect()
            ->route('staff.orders.index', ['locale' => $locale])
            ->with('success', __('staff-orders-completed'));
    }

    public function markServed(string $locale, Order $order, OrderItem $item): RedirectResponse
    {
        if ($order->id !== $item->order_id) {
            abort(404);
        }

        $item->is_served = ! $item->is_served;
        $item->save();

        $message = $item->is_served
            ? __('staff-orders-item-served')
            : __('staff-orders-item-not-served');

        return back()->with('success', $message);
    }

    public function cancel(string $locale, Order $order): RedirectResponse
    {
        if ($order->status !== Order::STATUS_OPEN) {
            return back()->with('error', __('staff-orders-cancel-forbidden'));
        }

        $order->status = Order::STATUS_CANCELLED;
        $order->completed_at = null;
        $order->save();

        $this->releaseTableIfFree($order);

        return redirect()
            ->route('staff.orders.index', ['locale' => $locale])
            ->with('success', __('staff-orders-cancelled'));
    }

    protected function releaseTableIfFree(Order $order): void
    {
        $table = $order->table;

        if ($table === null) {
            return;
        }

        $hasOtherOpenOrders = Order::where('table_id', $table->id)
            ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->where('id', '!=', $order->id)
            ->exists();

        $hasActiveReservations = Reservation::where('table_id', $table->id)
            ->whereIn('status', [
                Reservation::STATUS_PENDING,
                Reservation::STATUS_ACTIVE,
            ])
            ->exists();

        if (! $hasOtherOpenOrders && ! $hasActiveReservations) {
            $table->is_occupied = false;
            $table->save();
        }
    }
}
