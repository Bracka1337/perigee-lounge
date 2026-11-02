<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyEarning;
use App\Models\OpeningHour;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function index(Request $request, string $locale): View
    {
        Reservation::where('status', 'pending')
            ->where('start_at', '<', now()->subHour())
            ->update(['status' => 'expired']);

        $date = $request->query('date');
        if ($date === null || $date === '' || (is_array($date) && count($date) === 0)) {
            $date = Carbon::now()->toDateString();
        }

        $selectedDate = Carbon::parse($date)->startOfDay();

        $tables = Table::where('is_active', true)->orderBy('id')->get();

        $occupiedByTables = $tables->where('is_occupied', true)->pluck('id');
        $occupiedByOrders = Order::whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->pluck('table_id');

        $occupiedTableIds = $occupiedByTables
            ->merge($occupiedByOrders)
            ->unique()
            ->values()
            ->all();

        $reservationsForDay = Reservation::whereDate('start_at', $selectedDate->toDateString())
            ->whereIn('status', [
                Reservation::STATUS_PENDING,
                Reservation::STATUS_ACTIVE,
            ])
            ->get()
            ->groupBy('table_id');

        $list = Reservation::whereDate('start_at', $selectedDate->toDateString())
            ->orderBy('start_at')
            ->get();

        return view('staff.reservations.index', [
            'date' => $selectedDate,
            'tables' => $tables,
            'reservationsForDay' => $reservationsForDay,
            'reservations' => $list,
            'occupiedTableIds' => $occupiedTableIds,
        ]);
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'table_id' => ['required', 'integer', 'exists:tables,id'],
            'date' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i'],
            'mode' => ['required', 'in:phone,walkin'],
            'guest_name' => ['nullable', 'string', 'max:255'],
            'guest_phone' => ['nullable', 'string', 'max:255'],
            'guests_count' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $tableId = (int) $data['table_id'];

        $start = Carbon::parse($data['date'].' '.$data['time']);
        $now = Carbon::now();

        $startDate = $start->toDateString();
        $nowDate = $now->toDateString();

        if ($startDate < $nowDate) {
            return back()->with('error', __('staff-reservations-error-past'))->withInput();
        }

        if ($startDate === $nowDate && $start->lt($now)) {
            return back()->with('error', __('staff-reservations-error-past'))->withInput();
        }

        $table = Table::where('is_active', true)->findOrFail($tableId);

        if ($table->is_occupied) {
            return back()->with('error', __('staff-reservations-error-table-has-open-order'))->withInput();
        }

        $hasActiveOrder = Order::where('table_id', $tableId)
            ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->exists();

        if ($hasActiveOrder) {
            \Log::warning('Staff Reservation blocked: active order exists', [
                'table_id' => $tableId,
                'active_orders' => Order::where('table_id', $tableId)
                    ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
                    ->count(),
            ]);

            return back()->with('error', __('staff-reservations-error-table-has-open-order'))->withInput();
        }

        $weekday = $start->dayOfWeek;
        $hours = OpeningHour::where('weekday', $weekday)->first();

        if ($hours === null || ! $hours->is_open) {
            return back()->with('error', __('staff-reservations-error-closed'))->withInput();
        }

        $timeString = $start->format('H:i:s');
        $opensAt = $hours->opens_at;
        $closesAt = $hours->closes_at;

        if ($closesAt === '00:00:00') {
            $closesAt = '23:59:59';
        }

        if ($opensAt !== null && $opensAt !== '' && $closesAt !== null && $closesAt !== '') {
            if ($closesAt > $opensAt) {
                $isClosed = $timeString < $opensAt || $timeString >= $closesAt;
            } else {
                $isClosed = ! ($timeString >= $opensAt || $timeString < $closesAt);
            }

            if ($isClosed) {
                return back()->with('error', __('staff-reservations-error-closed'))->withInput();
            }
        }

        $tableConflict = Reservation::where('table_id', $tableId)
            ->whereDate('start_at', $startDate)
            ->whereIn('status', [
                Reservation::STATUS_PENDING,
                Reservation::STATUS_ACTIVE,
            ])
            ->exists();

        if ($tableConflict) {
            return back()->with('error', __('staff-reservations-error-overlap'))->withInput();
        }

        try {
            Reservation::create([
                'user_id' => null,
                'table_id' => $tableId,
                'guest_name' => (isset($data['guest_name']) && $data['guest_name'] !== '') ? $data['guest_name'] : null,
                'guest_phone' => (isset($data['guest_phone']) && $data['guest_phone'] !== '') ? $data['guest_phone'] : null,
                'start_at' => $start,
                'end_at' => null,
                'status' => Reservation::STATUS_PENDING,
                'guests_count' => $data['guests_count'],
                'source' => $data['mode'],
            ]);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('staff.reservations.index', [
                'locale' => $locale,
                'date' => $startDate,
            ])
            ->with('success', __('staff-reservations-created'));
    }

    public function markArrived(string $locale, Reservation $reservation): RedirectResponse
    {
        if (! in_array($reservation->status, [Reservation::STATUS_PENDING], true)) {
            return back()->with('error', __('staff-reservations-arrive-forbidden'));
        }

        $now = Carbon::now();

        if ($now->lt($reservation->start_at)) {
            return back()->with('error', __('staff-reservations-arrive-too-early'));
        }

        if ($now->gt($reservation->start_at->copy()->addHour())) {
            return back()->with('error', __('staff-reservations-arrive-too-late'));
        }

        $reservation->status = Reservation::STATUS_ACTIVE;
        $reservation->save();

        if ($reservation->order !== null) {
            $order = $reservation->order;
        } else {
            $existingOrder = Order::where('table_id', $reservation->table_id)
                ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
                ->first();

            if ($existingOrder !== null) {
                if (is_null($existingOrder->reservation_id)) {
                    $existingOrder->reservation_id = $reservation->id;
                    if ($existingOrder->source === null || $existingOrder->source === '') {
                        $existingOrder->source = ($reservation->source !== null && $reservation->source !== '') ? $reservation->source : 'reservation';
                    }
                    $existingOrder->save();
                    $order = $existingOrder;
                } else {
                    return back()->with('error', __('staff-reservations-error-table-has-open-order'));
                }
            } else {
                $order = Order::create([
                    'reservation_id' => $reservation->id,
                    'table_id' => $reservation->table_id,
                    'user_id' => $reservation->user_id,
                    'status' => Order::STATUS_OPEN,
                    'started_at' => Carbon::now(),
                    'source' => ($reservation->source !== null && $reservation->source !== '') ? $reservation->source : 'reservation',
                ]);
            }
        }

        if ($reservation->table !== null) {
            $reservation->table->is_occupied = true;
            $reservation->table->save();
        }

        return redirect()
            ->route('staff.orders.show', ['locale' => $locale, 'order' => $order])
            ->with('success', __('staff-reservations-arrived'));
    }

    public function markCompleted(string $locale, Reservation $reservation): RedirectResponse
    {
        if (! in_array($reservation->status, [Reservation::STATUS_ACTIVE], true)) {
            return back()->with('error', __('staff-reservations-complete-forbidden'));
        }

        DB::transaction(function () use ($reservation) {
            $now = Carbon::now();

            $reservation->status = Reservation::STATUS_COMPLETED;
            $reservation->end_at = $now;
            $reservation->save();

            // Prefer reservation->order, otherwise find any active order on the table
            $order = $reservation->order;
            if ($order === null) {
                $order = Order::where('table_id', $reservation->table_id)
                    ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
                    ->first();
            }

            if ($order !== null && $order->status !== Order::STATUS_COMPLETED) {
                $order->loadMissing('items', 'user');
                $order->recalculateTotals();
                $order->status = Order::STATUS_COMPLETED;
                $order->completed_at = $now;
                $order->save();

                if ($order->reservation !== null && in_array($order->reservation->status, [Reservation::STATUS_PENDING, Reservation::STATUS_ACTIVE], true)) {
                    $order->reservation->status = Reservation::STATUS_COMPLETED;
                    $order->reservation->end_at = $now;
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
            }

            // Release table if there are no other active orders or active reservations
            $table = $reservation->table;
            if ($table !== null) {
                $hasOtherOpenOrders = Order::where('table_id', $table->id)
                    ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
                    ->where('id', '!=', $order?->id)
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
        });

        return back()->with('success', __('staff-reservations-completed'));
    }

    public function cancel(string $locale, Reservation $reservation): RedirectResponse
    {
        if (! in_array($reservation->status, [Reservation::STATUS_PENDING, Reservation::STATUS_ACTIVE], true)) {
            return back()->with('error', __('staff-reservations-cancel-forbidden'));
        }

        if (! is_null($reservation->user_id)) {
            return back()->with('error', __('staff-reservations-cancel-client-forbidden'));
        }

        $reservation->status = Reservation::STATUS_CANCELLED;
        $reservation->end_at = null;
        $reservation->save();

        return back()->with('success', __('staff-reservations-cancelled'));
    }
}
