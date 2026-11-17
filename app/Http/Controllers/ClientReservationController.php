<?php

namespace App\Http\Controllers;

use App\Actions\Reservations\CreateRoomReservation;
use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Models\OpeningHour;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Table;
use App\Models\User;
use App\Notifications\ReservationCancelled;
use App\Notifications\ReservationCreated;
use App\Services\LoungeLayout;
use App\Services\LoungeRenders;
use App\Services\TableHolds;
use App\Services\TelegramNotifier;
use Carbon\Carbon;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ClientReservationController extends Controller
{
    public function index(Request $request, string $locale, #[CurrentUser] User $user, LoungeLayout $layout, LoungeRenders $renders, TableHolds $holds): View
    {
        $date = $this->dateFilter($request);

        $floors = $layout->floors();

        $reservations = Reservation::with('room.tables')->whereDate('start_at', $date->toDateString())
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

        $myReservations = Reservation::with(['table', 'room'])
            ->where('user_id', $user->id)
            ->orderByDesc('start_at')
            ->limit(10)
            ->get();

        return view('client.reservations.index', [
            'date' => $date,
            'floors' => $floors,
            'frame' => $layout->frame($floors),
            'lounge3d' => $layout->toArray($floors),
            'tableRenders' => $renders->urlsFor($floors),
            'roomRenders' => $renders->roomUrlsFor($floors),
            'roomBooking' => $holds->roomsOn($floors, $date->toDateString()),
            'activeFloorId' => $layout->activeFloorId($floors, $request->query('floor'), $request->old('table_id')),
            'reservations' => $reservations,
            'myReservations' => $myReservations,
            'occupiedTableIds' => $occupiedTableIds,
        ]);
    }

    public function store(Request $request, string $locale, #[CurrentUser] User $user, TableHolds $holds, CreateRoomReservation $bookRoom): RedirectResponse
    {
        if (! $user->hasVerifiedEmail()) {
            return back()->with('error', __('client-reservations-error-email'))->withInput();
        }

        $data = $request->validate([
            'table_id' => ['nullable', 'required_without:room_id', 'prohibits:room_id', 'integer', 'exists:tables,id'],
            'room_id' => ['nullable', 'required_without:table_id', 'integer', 'exists:rooms,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
            'guests_count' => ['required', 'integer', 'min:1', $request->filled('room_id') ? 'max:200' : 'max:20'],
        ]);

        if (($data['room_id'] ?? null) !== null) {
            return $this->storeRoom($data, $locale, $user, $bookRoom);
        }

        $table = Table::where('is_active', true)->findOrFail((int) $data['table_id']);

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

        // Also taken when the whole room around the table is booked that day.
        if ($holds->tableHeld($table->id, $startDate)) {
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

    /**
     * Books the whole room. The same rules as for a table apply: not in the past, at least an
     * hour ahead, within opening hours and one booking per guest per day.
     *
     * @param  array<string, mixed>  $data
     */
    private function storeRoom(array $data, string $locale, User $user, CreateRoomReservation $bookRoom): RedirectResponse
    {
        $start = Carbon::parse($data['date'].' '.$data['time']);

        $problem = match (true) {
            $start->toDateString() < Carbon::now()->toDateString() => 'client-reservations-error-past',
            $start->lt(Carbon::now()->addHour()) => 'client-reservations-error-too-soon',
            Reservation::where('user_id', $user->id)->whereDate('start_at', $start->toDateString())
                ->whereNotIn('status', [ReservationStatus::Cancelled, ReservationStatus::Completed, ReservationStatus::Expired])
                ->exists() => 'client-reservations-error-one-per-day',
            default => null,
        };
        if ($problem !== null) {
            return back()->with('error', __($problem))->withInput();
        }

        $reservation = $bookRoom->handle(Room::findOrFail((int) $data['room_id']), $start, (int) $data['guests_count'], [
            'user_id' => $user->id,
            'source' => 'client',
        ]);

        TelegramNotifier::notifyNewReservation($reservation);
        $user->notify(new ReservationCreated($reservation));

        return redirect()
            ->route('client.reservations.index', ['locale' => $locale])
            ->with('success', __('room-booking-created'));
    }

    public function cancel(Request $request, string $locale, Reservation $reservation, #[CurrentUser] User $user): RedirectResponse
    {
        Gate::authorize('cancel', $reservation);

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
