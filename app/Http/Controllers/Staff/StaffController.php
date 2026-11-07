<?php

namespace App\Http\Controllers\Staff;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        $tomorrow = Carbon::now()->addDay()->startOfDay();

        $tomorrowReservations = Reservation::whereDate('start_at', $tomorrow->toDateString())
            ->whereIn('status', ReservationStatus::holdingTable())
            ->with(['table', 'user'])
            ->orderBy('start_at')
            ->get();

        return view('staff.dashboard', [
            'tomorrow' => $tomorrow,
            'tomorrowReservations' => $tomorrowReservations,
        ]);
    }
}
