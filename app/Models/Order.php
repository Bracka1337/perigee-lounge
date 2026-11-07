<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

/**
 * @use HasFactory<OrderFactory>
 */
class Order extends Model
{
    use HasFactory, LogsActivity;

    public const STATUS_OPEN = 'open';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

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
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Reservation, Order>
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * @return BelongsTo<Table, Order>
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * @return BelongsTo<User, Order>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<OrderItem, Order>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return BelongsTo<PromoCode, Order>
     */
    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('total_price');
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

        $entries = LoyaltyEarning::where('order_id', $this->id)
            ->where('amount', '<', 0)
            ->orderByDesc('id')
            ->get();

        foreach ($entries as $entry) {
            if ($excess <= 0) {
                break;
            }

            $refund = min(abs((float) $entry->amount), $excess);

            if ($entry->user !== null) {
                $entry->user->loyalty_balance = round((float) $entry->user->loyalty_balance + $refund, 2);
                $entry->user->save();
            }

            $remaining = round((float) $entry->amount + $refund, 2);
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
        return $query->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    protected static function booted()
    {
        static::creating(function (Order $order) {
            // If the order is being created for an explicit reservation, allow it.
            if ($order->reservation_id !== null) {
                return;
            }

            $hasActiveReservation = Reservation::where('table_id', $order->table_id)
                ->whereIn('status', [Reservation::STATUS_PENDING, Reservation::STATUS_ACTIVE])
                ->exists();

            if ($hasActiveReservation) {
                throw ValidationException::withMessages([
                    'table_id' => [__('staff-orders-error-table-reserved')],
                ]);
            }
        });
    }
}
