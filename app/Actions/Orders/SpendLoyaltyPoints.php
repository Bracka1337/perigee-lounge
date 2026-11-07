<?php

namespace App\Actions\Orders;

use App\Exceptions\BusinessRuleException;
use App\Models\LoyaltyEarning;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Spends the order client's loyalty points as a discount, up to their balance and
 * the order total. Spending is stored as a negative loyalty entry on the order.
 */
class SpendLoyaltyPoints
{
    public function handle(Order $order, float $amount): void
    {
        $user = $order->user;

        if ($user === null) {
            throw new BusinessRuleException('staff-orders-apply-loyalty-no-client');
        }

        if ($amount > min($user->loyalty_balance ?? 0, $order->total ?? 0)) {
            throw new BusinessRuleException('staff-orders-apply-loyalty-exceeds-limit');
        }

        if ($amount <= 0) {
            throw new BusinessRuleException('staff-orders-apply-loyalty-invalid-amount');
        }

        DB::transaction(function () use ($order, $user, $amount) {
            $user->loyalty_balance = max(0, ($user->loyalty_balance ?? 0) - $amount);
            $user->save();

            LoyaltyEarning::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'amount' => -$amount,
            ]);

            $order->recalculateTotals();
            $order->save();
        });
    }
}
