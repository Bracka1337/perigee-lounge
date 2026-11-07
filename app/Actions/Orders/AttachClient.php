<?php

namespace App\Actions\Orders;

use App\Exceptions\BusinessRuleException;
use App\Models\Order;
use App\Models\User;

/**
 * Links a client to an order by their loyalty code. The client can't be swapped
 * once points were spent on the order, so points can't move between accounts.
 */
class AttachClient
{
    public function handle(Order $order, string $loyaltyCode): void
    {
        $user = User::where('loyalty_code', $loyaltyCode)->first();

        if ($user === null) {
            throw new BusinessRuleException('staff-orders-attach-client-not-found');
        }

        if ($order->user_id !== $user->id && $order->loyaltySpent() > 0) {
            throw new BusinessRuleException('staff-orders-attach-client-loyalty-spent');
        }

        $order->user_id = $user->id;
        $order->save();
    }
}
