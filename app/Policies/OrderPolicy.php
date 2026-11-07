<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Clients see only their own orders. Others get a 404, so order ids can't be probed.
     */
    public function view(User $user, Order $order): Response
    {
        return $order->user_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Staff add items only while the order is open.
     */
    public function addItems(User $user, Order $order): bool
    {
        return $user->isWorker() && $order->status === Order::STATUS_OPEN;
    }
}
