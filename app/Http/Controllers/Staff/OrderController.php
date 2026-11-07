<?php

namespace App\Http\Controllers\Staff;

use App\Actions\Orders\ApplyPromoCode;
use App\Actions\Orders\AttachClient;
use App\Actions\Orders\CompleteOrder;
use App\Actions\Orders\OpenReservationOrder;
use App\Actions\Orders\OpenTableOrder;
use App\Actions\Orders\SpendLoyaltyPoints;
use App\Actions\Reservations\ExpireLateReservations;
use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ApplyPromoCodeRequest;
use App\Http\Requests\Staff\AttachClientRequest;
use App\Http\Requests\Staff\SpendLoyaltyRequest;
use App\Http\Requests\Staff\StoreOrderRequest;
use App\Http\Requests\Staff\UpdateOrderItemRequest;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Reservation;
use App\Models\Table;
use App\Notifications\OrderCompleted;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request, string $locale): View
    {
        $selectedDate = $this->dateFilter($request);

        return view('staff.orders.index', [
            'date' => $selectedDate,
            'tables' => Table::where('is_active', true)->orderBy('id')->get(),
            // Open orders show regardless of date; history only for the selected day.
            'ordersActive' => Order::active()->with(['table', 'user'])->orderByDesc('created_at')->get(),
            'ordersHistory' => Order::with(['table', 'user'])
                ->whereDate('created_at', $selectedDate->toDateString())
                ->whereIn('status', OrderStatus::closed())
                ->orderByDesc('created_at')
                ->get(),
            'reservations' => Reservation::whereDate('start_at', $selectedDate->toDateString())
                ->whereIn('status', ReservationStatus::holdingTable())
                ->whereDoesntHave('order')
                ->with(['table', 'user'])
                ->orderBy('start_at')
                ->get(),
        ]);
    }

    public function create(string $locale): View
    {
        return view('staff.orders.create', [
            'tables' => Table::where('is_active', true)->orderBy('id')->get(),
            'menuCategories' => MenuCategory::with('items')->ordered()->get(),
            'reservedTableIds' => Reservation::whereDate('start_at', Carbon::now()->toDateString())
                ->whereIn('status', ReservationStatus::holdingTable())
                ->pluck('table_id')
                ->toArray(),
            'openOrderTableIds' => Order::active()->pluck('table_id')->toArray(),
        ]);
    }

    public function store(StoreOrderRequest $request, string $locale, OpenTableOrder $openTableOrder): RedirectResponse
    {
        $table = Table::where('is_active', true)->findOrFail((int) $request->validated('table_id'));

        $order = $openTableOrder->handle($table, $request->validated('user_id'));

        return redirect()->route('staff.orders.show', ['locale' => $locale, 'order' => $order]);
    }

    public function fromReservation(string $locale, Reservation $reservation, OpenReservationOrder $openReservationOrder): RedirectResponse
    {
        if ($reservation->order !== null) {
            return redirect()
                ->route('staff.orders.show', ['locale' => $locale, 'order' => $reservation->order])
                ->with('success', __('staff-orders-order-already-created'));
        }

        $backToDay = fn (string $error) => redirect()
            ->route('staff.orders.index', ['locale' => $locale, 'date' => $reservation->start_at->toDateString()])
            ->with('error', __($error));

        $late = $reservation->status === ReservationStatus::Pending
            && Carbon::now()->gt($reservation->start_at->copy()->addMinutes(ExpireLateReservations::GRACE_MINUTES));

        if (! in_array($reservation->status, ReservationStatus::holdingTable(), true) || $late) {
            return $backToDay('staff-orders-error-reservation-closed');
        }

        if (Carbon::now()->lt($reservation->start_at)) {
            return $backToDay('staff-orders-error-table-reserved');
        }

        $order = $openReservationOrder->handle($reservation);

        if ($order === null) {
            return $backToDay('staff-orders-error-table-has-open-order');
        }

        if ($order->user_id === null && $reservation->user_id !== null) {
            $order->user_id = $reservation->user_id;
            $order->save();
        }

        return redirect()
            ->route('staff.orders.show', ['locale' => $locale, 'order' => $order])
            ->with('success', __('staff-orders-order-created'));
    }

    public function show(string $locale, Order $order): View
    {
        $order->load(['items.menuItem.category', 'table', 'user', 'promoCode']);

        return view('staff.orders.show', [
            'order' => $order,
            'menuCategories' => MenuCategory::with('items')->ordered()->get(),
            'loyaltySpent' => $order->loyaltySpent(),
        ]);
    }

    public function updateItem(UpdateOrderItemRequest $request, string $locale, Order $order, OrderItem $item): RedirectResponse
    {
        abort_if($order->id !== $item->order_id, 404);

        if ($order->status !== OrderStatus::Open) {
            return back()->with('error', __('staff-orders-update-item-forbidden'));
        }

        DB::transaction(function () use ($order, $item, $request) {
            $item->quantity = $request->validated('quantity');
            $item->total_price = $item->unit_price * $item->quantity;
            $item->save();

            $order->syncTotals();
        });

        return back()->with('success', __('staff-orders-item-updated'));
    }

    public function destroyItem(string $locale, Order $order, OrderItem $item): RedirectResponse
    {
        abort_if($order->id !== $item->order_id, 404);

        if ($order->status !== OrderStatus::Open) {
            return back()->with('error', __('staff-orders-delete-item-forbidden'));
        }

        DB::transaction(function () use ($order, $item) {
            $item->delete();
            $order->syncTotals();
        });

        return back()->with('success', __('staff-orders-item-deleted'));
    }

    public function attachClient(AttachClientRequest $request, string $locale, Order $order, AttachClient $attachClient): RedirectResponse
    {
        if ($order->status !== OrderStatus::Open) {
            return back()->with('error', __('staff-orders-attach-client-forbidden'));
        }

        $attachClient->handle($order, $request->validated('loyalty_code'));

        return back()->with('success', __('staff-orders-attach-client-success'));
    }

    public function applyPromo(ApplyPromoCodeRequest $request, string $locale, Order $order, ApplyPromoCode $applyPromoCode): RedirectResponse
    {
        if ($order->status !== OrderStatus::Open) {
            return back()->with('error', __('staff-orders-apply-promo-forbidden'));
        }

        $applyPromoCode->handle($order, $request->validated('code'));

        return back()->with('success', __('staff-orders-promo-applied'));
    }

    public function applyLoyalty(SpendLoyaltyRequest $request, string $locale, Order $order, SpendLoyaltyPoints $spendLoyaltyPoints): RedirectResponse
    {
        if ($order->status !== OrderStatus::Open) {
            return back()->with('error', __('staff-orders-apply-loyalty-forbidden'));
        }

        $spendLoyaltyPoints->handle($order, (float) $request->validated('amount'));

        return back()->with('success', __('staff-orders-apply-loyalty-applied'));
    }

    public function complete(string $locale, Order $order, CompleteOrder $completeOrder): RedirectResponse
    {
        if ($order->status !== OrderStatus::Open) {
            return back()->with('error', __('staff-orders-complete-forbidden'));
        }

        $completeOrder->handle($order);

        $order->user?->notify(new OrderCompleted($order));
        $order->table?->releaseIfFree($order->id);

        return redirect()
            ->route('staff.orders.index', ['locale' => $locale])
            ->with('success', __('staff-orders-completed'));
    }

    public function markServed(string $locale, Order $order, OrderItem $item): RedirectResponse
    {
        abort_if($order->id !== $item->order_id, 404);

        $item->is_served = ! $item->is_served;
        $item->save();

        return back()->with('success', __($item->is_served ? 'staff-orders-item-served' : 'staff-orders-item-not-served'));
    }
}
