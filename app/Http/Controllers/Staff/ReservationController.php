<?php

namespace App\Http\Controllers\Staff;

use App\Actions\Orders\CompleteOrder;
use App\Actions\Orders\OpenReservationOrder;
use App\Actions\Reservations\CreateStaffReservation;
use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreReservationRequest;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReservationController extends Controller
{
    public function index(Request $request, string $locale): View
    {
        // Guests more than an hour late lose the booking.
        Reservation::where('status', ReservationStatus::Pending)
            ->where('start_at', '<', now()->subHour())
            ->update(['status' => ReservationStatus::Expired]);

        $selectedDate = $this->dateFilter($request);
        $tables = Table::where('is_active', true)->orderBy('id')->get();

        $occupiedTableIds = $tables->where('is_occupied', true)->pluck('id')
            ->merge(Order::active()->pluck('table_id'))
            ->unique()
            ->values()
            ->all();

        return view('staff.reservations.index', [
            'date' => $selectedDate,
            'tables' => $tables,
            'reservationsForDay' => Reservation::whereDate('start_at', $selectedDate->toDateString())
                ->whereIn('status', ReservationStatus::holdingTable())
                ->get()
                ->groupBy('table_id'),
            'reservations' => Reservation::with('table')
                ->whereDate('start_at', $selectedDate->toDateString())
                ->orderBy('start_at')
                ->get(),
            'occupiedTableIds' => $occupiedTableIds,
        ]);
    }

    public function store(StoreReservationRequest $request, string $locale, CreateStaffReservation $createReservation): RedirectResponse
    {
        $reservation = $createReservation->handle($request->validated());

        return redirect()
            ->route('staff.reservations.index', [
                'locale' => $locale,
                'date' => $reservation->start_at->toDateString(),
            ])
            ->with('success', __('staff-reservations-created'));
    }

    public function markArrived(string $locale, Reservation $reservation, OpenReservationOrder $openReservationOrder): RedirectResponse
    {
        if ($reservation->status !== ReservationStatus::Pending) {
            return back()->with('error', __('staff-reservations-arrive-forbidden'));
        }

        $now = Carbon::now();

        if ($now->lt($reservation->start_at)) {
            return back()->with('error', __('staff-reservations-arrive-too-early'));
        }

        if ($now->gt($reservation->start_at->copy()->addHour())) {
            return back()->with('error', __('staff-reservations-arrive-too-late'));
        }

        $order = $openReservationOrder->handle($reservation);

        if ($order === null) {
            return back()->with('error', __('staff-reservations-error-table-has-open-order'));
        }

        return redirect()
            ->route('staff.orders.show', ['locale' => $locale, 'order' => $order])
            ->with('success', __('staff-reservations-arrived'));
    }

    public function markCompleted(string $locale, Reservation $reservation, CompleteOrder $completeOrder): RedirectResponse
    {
        if ($reservation->status !== ReservationStatus::Active) {
            return back()->with('error', __('staff-reservations-complete-forbidden'));
        }

        DB::transaction(function () use ($reservation, $completeOrder) {
            $reservation->status = ReservationStatus::Completed;
            $reservation->end_at = Carbon::now();
            $reservation->save();

            // The reservation's own order, or else whatever order is open on the table.
            $order = $reservation->order
                ?? Order::active()->where('table_id', $reservation->table_id)->first();

            if ($order !== null && $order->status !== OrderStatus::Completed) {
                $completeOrder->handle($order);
            }

            $reservation->table?->releaseIfFree($order?->id);
        });

        return back()->with('success', __('staff-reservations-completed'));
    }

    public function cancel(string $locale, Reservation $reservation): RedirectResponse
    {
        if (! in_array($reservation->status, ReservationStatus::holdingTable(), true)) {
            return back()->with('error', __('staff-reservations-cancel-forbidden'));
        }

        if (Gate::denies('cancel', $reservation)) {
            return back()->with('error', __('staff-reservations-cancel-client-forbidden'));
        }

        $reservation->status = ReservationStatus::Cancelled;
        $reservation->end_at = null;
        $reservation->save();

        return back()->with('success', __('staff-reservations-cancelled'));
    }
}
