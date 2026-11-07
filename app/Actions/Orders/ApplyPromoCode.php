<?php

namespace App\Actions\Orders;

use App\Exceptions\BusinessRuleException;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use Illuminate\Support\Facades\DB;

/**
 * Applies a promo code (typed public code or scanned QR code) to an open order
 * and records its use. One promo code per order.
 */
class ApplyPromoCode
{
    public function handle(Order $order, string $code): void
    {
        if ($order->promo_code_id !== null) {
            throw new BusinessRuleException('staff-orders-promo-already-applied');
        }

        $promo = PromoCode::where('public_code', $code)
            ->orWhere('qr_code', $code)
            ->first();

        if ($promo === null || ! $promo->isAvailable()) {
            throw new BusinessRuleException('staff-orders-promo-invalid');
        }

        $order->loadMissing('items.menuItem');

        $discount = $promo->calculateDiscountForOrder($order);
        if ($discount <= 0) {
            throw new BusinessRuleException('staff-orders-promo-no-eligible-items');
        }

        DB::transaction(function () use ($order, $promo, $discount) {
            $order->promo_code_id = $promo->id;
            $order->refundExcessLoyalty();
            $order->recalculateTotals();
            $order->save();

            PromoCodeUsage::create([
                'promo_code_id' => $promo->id,
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'discount_amount' => $discount,
            ]);

            $promo->used_count++;
            $promo->save();
        });
    }
}
