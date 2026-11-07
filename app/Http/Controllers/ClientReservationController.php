<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Models\OpeningHour;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Table;
use App\Notifications\ReservationCancelled;
use App\Notifications\ReservationCreated;
use App\Services\TelegramNotifier;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ClientReservationController extends Controller
{
    public function index(Request $request, string $locale): View
    {
        Reservation::where('status', ReservationStatus::Pending)
            ->where('start_at', '<', now()->subHour())
            ->update(['status' => ReservationStatus::Expired]);

        $user = $request->user();

        $date = $this->dateFilter($request);

        $tables = Table::where('is_active', true)
            ->orderBy('id')
            ->get();

        $reservations = Reservation::whereDate('start_at', $date->toDateString())
            ->whereIn('status', ReservationStatus::holdingTable())
            ->get();

        $occupiedByOrders = Order::whereNotIn('status', OrderStatus::closed())
            ->pluck('table_id');

        // occupiedTableIds now only includes active orders, not reservations
        // Reservations are checked separately in the view by status
        $occupiedTableIds = $occupiedByOrders
            ->unique()
            ->values()
            ->all();

        $myReservations = Reservation::where('user_id', $user->id)
            ->orderByDesc('start_at')
            ->limit(10)
            ->get();

        return view('client.reservations.index', [
            'date' => $date,
            'tables' => $tables,
            'reservations' => $reservations,
            'myReservations' => $myReservations,
            'occupiedTableIds' => $occupiedTableIds,
        ]);
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        $user = $request->user();

        if (! ($user instanceof MustVerifyEmail) || ! $user->hasVerifiedEmail()) {
            return back()->with('error', __('client-reservations-error-email'))->withInput();
        }

        $data = $request->validate([
            'table_id' => ['required', 'exists:tables,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
            'guests_count' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $table = Table::where('is_active', true)->findOrFail($data['table_id']);

        if ($table->is_occupied) {
            return back()->with('error', __('client-reservations-error-overlap'))->withInput();
        }

        $start = Carbon::parse($data['date'].' '.$data['time']);
        $now = Carbon::now();
        $minStart = $now->copy()->addHour();

        $startDate = $start->toDateString();
        $nowDate = $now->toDateString();

        if ($startDate < $nowDate) {
            return back()->with('error', __('client-reservations-error-past'))->withInput();
        }

        if ($start->lt($minStart)) {
            return back()->with('error', __('client-reservations-error-too-soon'))->withInput();
        }

        if (! OpeningHour::isOpenAt($start)) {
            return back()->with('error', __('client-reservations-error-closed'))->withInput();
        }

        $hasReservationThatDay = Reservation::where('user_id', $user->id)
            ->whereDate('start_at', $startDate)
            ->whereNotIn('status', [
                ReservationStatus::Cancelled,
                ReservationStatus::Completed,
                ReservationStatus::Expired,
            ])
            ->exists();

        if ($hasReservationThatDay) {
            return back()->with('error', __('client-reservations-error-one-per-day'))->withInput();
        }

        $hasActiveOrder = Order::where('table_id', $table->id)
            ->whereNotIn('status', OrderStatus::closed())
            ->exists();

        if ($hasActiveOrder) {
            \Log::warning('Reservation blocked: active order exists', [
                'table_id' => $table->id,
                'user_id' => $user->id,
                'active_orders' => Order::where('table_id', $table->id)
                    ->whereNotIn('status', OrderStatus::closed())
                    ->count(),
            ]);

            return back()->with('error', __('client-reservations-error-overlap'))->withInput();
        }

        $tableConflict = Reservation::where('table_id', $table->id)
            ->whereDate('start_at', $startDate)
            ->whereIn('status', ReservationStatus::holdingTable())
            ->exists();

        if ($tableConflict) {
            return back()->with('error', __('client-reservations-error-overlap'))->withInput();
        }

        try {
            $reservation = Reservation::create([
                'user_id' => $user->id,
                'table_id' => $table->id,
                'guest_name' => null,
                'guest_phone' => null,
                'start_at' => $start,
                'end_at' => null,
                'status' => ReservationStatus::Pending,
                'guests_count' => $data['guests_count'],
                'source' => 'client',
            ]);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        TelegramNotifier::notifyNewReservation($reservation);
        $user->notify(new ReservationCreated($reservation));

        return redirect()
            ->route('client.reservations.index', ['locale' => $locale])
            ->with('success', __('client-reservations-created'));
    }

    public function cancel(Request $request, string $locale, Reservation $reservation): RedirectResponse
    {
        Gate::authorize('cancel', $reservation);

        $user = $request->user();

        if (! in_array($reservation->status, [ReservationStatus::Pending], true)) {
            return back()->with('error', __('client-reservations-cancel-forbidden'));
        }

        $reservation->status = ReservationStatus::Cancelled;
        $reservation->end_at = null;
        $reservation->save();

        TelegramNotifier::notifyCancelledReservation($reservation);
        $user->notify(new ReservationCancelled($reservation));

        return redirect()->route('client.reservations.index', ['locale' => $locale])
            ->with('success', __('client-reservations-cancelled'));
    }
}
