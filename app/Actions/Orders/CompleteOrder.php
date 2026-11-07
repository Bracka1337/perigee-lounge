<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Models\LoyaltyEarning;
use App\Models\Order;
use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Closes an order with its final totals, completes its reservation if the guest
 * is still there, and credits the client 1% of the total in loyalty points.
 */
class CompleteOrder
{
    public const LOYALTY_RATE = 0.01;

    public function handle(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $now = Carbon::now();

            $order->loadMissing('items', 'reservation', 'user');
            $order->recalculateTotals();
            $order->status = OrderStatus::Completed;
            $order->completed_at = $now;
            $order->save();

            $reservation = $order->reservation;
            if ($reservation !== null && in_array($reservation->status, ReservationStatus::holdingTable(), true)) {
                $reservation->status = ReservationStatus::Completed;
                $reservation->end_at = $now;
                $reservation->save();
            }

            $this->creditLoyalty($order);
        });
    }

    private function creditLoyalty(Order $order): void
    {
        $user = $order->user;
        if ($user === null || $order->total <= 0) {
            return;
        }

        $bonus = round($order->total * self::LOYALTY_RATE, 2);
        if ($bonus <= 0) {
            return;
        }

        $user->loyalty_balance = ($user->loyalty_balance ?? 0) + $bonus;
        $user->save();

        LoyaltyEarning::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => $bonus,
        ]);
    }
}
