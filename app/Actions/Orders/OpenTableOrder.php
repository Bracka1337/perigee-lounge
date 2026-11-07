<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Support\Carbon;

/**
 * Staff opens an order at a table. If today's reservation for the table has started,
 * the order is linked to it and the guest is marked as seated.
 */
class OpenTableOrder
{
    public function handle(Table $table, ?int $userId): Order
    {
        if ($table->is_occupied) {
            throw new BusinessRuleException('staff-orders-error-table-occupied');
        }

        if (Order::active()->where('table_id', $table->id)->exists()) {
            throw new BusinessRuleException('staff-orders-error-table-has-open-order');
        }

        $now = Carbon::now();

        $reservation = Reservation::where('table_id', $table->id)
            ->whereDate('start_at', $now->toDateString())
            ->whereIn('status', ReservationStatus::holdingTable())
            ->orderBy('start_at')
            ->first();

        if ($reservation !== null && $now->lt($reservation->start_at)) {
            throw new BusinessRuleException('staff-orders-error-table-reserved');
        }

        if ($reservation?->status === ReservationStatus::Pending) {
            $reservation->status = ReservationStatus::Active;
            $reservation->save();
        }

        $order = Order::create([
            'reservation_id' => $reservation?->id,
            'table_id' => $table->id,
            'user_id' => $userId ?? $reservation?->user_id,
            'status' => OrderStatus::Open,
            'started_at' => $now,
            'source' => ($reservation->source ?? '') !== '' ? $reservation->source : 'manual',
        ]);

        $table->occupy();

        return $order;
    }
}
