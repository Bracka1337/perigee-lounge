<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\LoyaltyEarning;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();
        $from7 = Carbon::now()->subDays(7);
        $from30 = Carbon::now()->subDays(30);

        $revenueToday = Order::where('status', OrderStatus::Completed)
            ->whereDate('completed_at', $today)
            ->sum('total');

        $revenue7 = Order::where('status', OrderStatus::Completed)
            ->where('completed_at', '>=', $from7)
            ->sum('total');

        $revenue30 = Order::where('status', OrderStatus::Completed)
            ->where('completed_at', '>=', $from30)
            ->sum('total');

        $ordersToday = Order::where('status', OrderStatus::Completed)
            ->whereDate('completed_at', $today)
            ->count();

        $orders7 = Order::where('status', OrderStatus::Completed)
            ->where('completed_at', '>=', $from7)
            ->count();

        $orders30 = Order::where('status', OrderStatus::Completed)
            ->where('completed_at', '>=', $from30)
            ->count();

        $aov30 = $orders30 > 0 ? round($revenue30 / $orders30, 2) : 0;

        $discount30 = Order::where('status', OrderStatus::Completed)
            ->where('completed_at', '>=', $from30)
            ->sum('discount_total');

        $ordersWithPromo30 = Order::where('status', OrderStatus::Completed)
            ->where('completed_at', '>=', $from30)
            ->whereNotNull('promo_code_id')
            ->count();

        $promoShare30 = $orders30 > 0 ? round(($ordersWithPromo30 / $orders30) * 100, 1) : 0;

        $loyaltyEarned30 = LoyaltyEarning::where('created_at', '>=', $from30)->sum('amount');

        $activeOrdersNow = Order::where('status', OrderStatus::Open)->count();
        $activeReservationsNow = Reservation::whereIn('status', ReservationStatus::holdingTable())->count();

        $revenueByDay = Order::selectRaw('DATE(completed_at) as d, SUM(total) as sum')
            ->where('status', OrderStatus::Completed)
            ->where('completed_at', '>=', $from30)
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        $ordersByDay = Order::selectRaw('DATE(completed_at) as d, COUNT(*) as cnt')
            ->where('status', OrderStatus::Completed)
            ->where('completed_at', '>=', $from30)
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        $topPromos = Order::select('promo_code_id')
            ->selectRaw('COUNT(*) as uses, SUM(discount_total) as discount_sum')
            ->where('status', OrderStatus::Completed)
            ->where('completed_at', '>=', $from30)
            ->whereNotNull('promo_code_id')
            ->groupBy('promo_code_id')
            ->orderByDesc('discount_sum')
            ->limit(10)
            ->with('promoCode:id,public_code')
            ->get();

        $topClients = User::where('role', User::ROLE_CLIENT)
            ->withSum(['orders as spent' => function ($q) use ($from30) {
                $q->where('status', OrderStatus::Completed)
                    ->where('completed_at', '>=', $from30);
            }], 'total')
            ->orderByDesc('spent')
            ->limit(10)
            ->get(['id', 'name', 'email']);

        $newClients30 = User::where('role', User::ROLE_CLIENT)
            ->where('created_at', '>=', $from30)
            ->count();

        return view('admin.dashboard', compact(
            'revenueToday', 'revenue7', 'revenue30',
            'ordersToday', 'orders7', 'orders30',
            'aov30', 'discount30', 'promoShare30',
            'loyaltyEarned30', 'activeOrdersNow', 'activeReservationsNow',
            'revenueByDay', 'ordersByDay', 'topPromos', 'topClients', 'newClients30'
        ));
    }
}
