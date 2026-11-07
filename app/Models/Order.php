<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Order extends Model
{
    use LogsActivity;

    protected $fillable = [
        'reservation_id',
        'table_id',
        'user_id',
        'status',
        'subtotal',
        'discount_total',
        'total',
        'started_at',
        'completed_at',
        'source',
        'promo_code_id',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'subtotal' => 'float',
        'discount_total' => 'float',
        'total' => 'float',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Reservation, $this>
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * @return BelongsTo<Table, $this>
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return BelongsTo<PromoCode, $this>
     */
    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    /**
     * Reloads the order after its items or promo changed, gives back loyalty points
     * that no longer fit, and saves the new totals.
     */
    public function syncTotals(): void
    {
        $this->refresh();
        $this->refundExcessLoyalty();
        $this->recalculateTotals();
        $this->save();
    }

    public function recalculateTotals(): void
    {
        $subtotal = (float) $this->items()->sum('total_price');
        $this->subtotal = $subtotal;
        $this->discount_total = $this->promoDiscount() + $this->loyaltySpent();
        $this->total = max($subtotal - $this->discount_total, 0);
    }

    /**
     * Loyalty discounts are stored as negative LoyaltyEarning.amount entries linked to this order.
     */
    public function loyaltySpent(): float
    {
        $spent = LoyaltyEarning::where('order_id', $this->id)
            ->where('amount', '<', 0)
            ->sum('amount');

        return abs((float) $spent);
    }

    /**
     * Gives back points the order can no longer use after its items or promo code changed,
     * newest spend first. Each spend entry shrinks to what the order still uses.
     */
    public function refundExcessLoyalty(): void
    {
        $payable = max((float) $this->items()->sum('total_price') - $this->promoDiscount(), 0);
        $excess = round($this->loyaltySpent() - $payable, 2);

        if ($excess <= 0) {
            return;
        }

        $entries = LoyaltyEarning::with('user')
            ->where('order_id', $this->id)
            ->where('amount', '<', 0)
            ->orderByDesc('id')
            ->get();

        foreach ($entries as $entry) {
            if ($excess <= 0) {
                break;
            }

            $refund = min(abs($entry->amount), $excess);

            if ($entry->user !== null) {
                $entry->user->loyalty_balance = round($entry->user->loyalty_balance + $refund, 2);
                $entry->user->save();
            }

            $remaining = round($entry->amount + $refund, 2);
            if ($remaining < 0) {
                $entry->update(['amount' => $remaining]);
            } else {
                $entry->delete();
            }

            $excess = round($excess - $refund, 2);
        }
    }

    private function promoDiscount(): float
    {
        return $this->promoCode?->calculateDiscountForOrder($this) ?? 0.0;
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', OrderStatus::closed());
    }

    protected static function booted()
    {
        static::creating(function (Order $order) {
            // If the order is being created for an explicit reservation, allow it.
            if ($order->reservation_id !== null) {
                return;
            }

            $hasActiveReservation = Reservation::where('table_id', $order->table_id)
                ->whereIn('status', ReservationStatus::holdingTable())
                ->exists();

            if ($hasActiveReservation) {
                throw ValidationException::withMessages([
                    'table_id' => [__('staff-orders-error-table-reserved')],
                ]);
            }
        });
    }
}
