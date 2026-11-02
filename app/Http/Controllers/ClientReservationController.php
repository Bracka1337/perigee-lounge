<?php

namespace App\Http\Controllers;

use App\Models\OpeningHour;
use App\Models\Reservation;
use App\Models\Table;
use App\Models\Order;
use App\Services\TelegramNotifier;
use App\Notifications\ReservationCreated;
use App\Notifications\ReservationCancelled;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientReservationController extends Controller
{
    public function index(Request $request, string $locale): View
    {
        Reservation::where('status', 'pending')
            ->where('start_at', '<', now()->subHour())
            ->update(['status' => 'expired']);

        $user = $request->user();

        $dateParam = $request->query('date');
        if ($dateParam === null || $dateParam === '' || (is_array($dateParam) && count($dateParam) === 0)) {
            $dateParam = Carbon::now()->toDateString();
        }

        $date = Carbon::parse($dateParam)->startOfDay();

        $tables = Table::where('is_active', true)
            ->orderBy('id')
            ->get();

        $reservations = Reservation::whereDate('start_at', $date->toDateString())
            ->whereIn('status', [
                Reservation::STATUS_PENDING,
                Reservation::STATUS_ACTIVE,
            ])
            ->get();

        $occupiedByOrders = Order::whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
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

        if (!($user instanceof MustVerifyEmail) || !$user->hasVerifiedEmail()) {
            return back()->with('error', __('client-reservations-error-email'))->withInput();
        }

        $data = $request->validate([
            'table_id'     => ['required', 'exists:tables,id'],
            'date'         => ['required', 'date'],
            'time'         => ['required', 'date_format:H:i'],
            'guests_count' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $table = Table::where('is_active', true)->findOrFail($data['table_id']);

        if ($table->is_occupied) {
            return back()->with('error', __('client-reservations-error-overlap'))->withInput();
        }

        $start = Carbon::parse($data['date'] . ' ' . $data['time']);
        $now   = Carbon::now();
        $minStart = $now->copy()->addHour();

        $startDate = $start->toDateString();
        $nowDate   = $now->toDateString();

        if ($startDate < $nowDate) {
            return back()->with('error', __('client-reservations-error-past'))->withInput();
        }

        if ($start->lt($minStart)) {
            return back()->with('error', __('client-reservations-error-too-soon'))->withInput();
        }

        $weekday = $start->dayOfWeek;
        $hours   = OpeningHour::where('weekday', $weekday)->first();

        if ($hours === null || !$hours->is_open) {
            return back()->with('error', __('client-reservations-error-closed'))->withInput();
        }

        $timeString = $start->format('H:i:s');
        $opensAt    = $hours->opens_at;
        $closesAt   = $hours->closes_at;

        if ($closesAt === '00:00:00') {
            $closesAt = '23:59:59';
        }

        if ($opensAt !== null && $opensAt !== '' && $closesAt !== null && $closesAt !== '') {
            if ($closesAt > $opensAt) {
                $isClosed = $timeString < $opensAt || $timeString >= $closesAt;
            } else {
                $isClosed = !($timeString >= $opensAt || $timeString < $closesAt);
            }

            if ($isClosed) {
                return back()->with('error', __('client-reservations-error-closed'))->withInput();
            }
        }

        $hasReservationThatDay = Reservation::where('user_id', $user->id)
            ->whereDate('start_at', $startDate)
            ->whereNotIn('status', [
                Reservation::STATUS_CANCELLED,
                Reservation::STATUS_COMPLETED,
                Reservation::STATUS_EXPIRED,
            ])
            ->exists();

        if ($hasReservationThatDay) {
            return back()->with('error', __('client-reservations-error-one-per-day'))->withInput();
        }

        $hasActiveOrder = Order::where('table_id', $table->id)
            ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->exists();

        if ($hasActiveOrder) {
            \Log::warning('Reservation blocked: active order exists', [
                'table_id' => $table->id,
                'user_id' => $user->id,
                'active_orders' => Order::where('table_id', $table->id)
                    ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
                    ->count(),
            ]);
            return back()->with('error', __('client-reservations-error-overlap'))->withInput();
        }

        $tableConflict = Reservation::where('table_id', $table->id)
            ->whereDate('start_at', $startDate)
            ->whereIn('status', [
                Reservation::STATUS_PENDING,
                Reservation::STATUS_ACTIVE,
            ])
            ->exists();

        if ($tableConflict) {
            return back()->with('error', __('client-reservations-error-overlap'))->withInput();
        }

        try {
            $reservation = Reservation::create([
                'user_id'      => $user->id,
                'table_id'     => $table->id,
                'guest_name'   => null,
                'guest_phone'  => null,
                'start_at'     => $start,
                'end_at'       => null,
                'status'       => Reservation::STATUS_PENDING,
                'guests_count' => $data['guests_count'],
                'source'       => 'client',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
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
        $user = $request->user();

        if ($reservation->user_id !== $user->id) {
            abort(403);
        }

        if (!in_array($reservation->status, [Reservation::STATUS_PENDING], true)) {
            return back()->with('error', __('client-reservations-cancel-forbidden'));
        }

        $reservation->status = Reservation::STATUS_CANCELLED;
        $reservation->end_at = null;
        $reservation->save();

        TelegramNotifier::notifyCancelledReservation($reservation);
        $user->notify(new ReservationCancelled($reservation));

        return redirect()->route('client.reservations.index', ['locale' => $locale])
            ->with('success', __('client-reservations-cancelled'));
    }
}
