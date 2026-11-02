<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;
use App\Models\Reservation;
use App\Models\LoyaltyEarning;
use App\Models\Concerns\LogsActivity;

/**
 * @use HasFactory<\Database\Factories\OrderFactory>
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
        $promoDiscount = 0;
        if ($this->promoCode !== null) {
            $promoDiscount = $this->promoCode->calculateDiscountForOrder($this);
        }

        // Loyalty discounts are stored as negative LoyaltyEarning.amount entries linked to this order.
        $loyaltyNegativeSum = LoyaltyEarning::where('order_id', $this->id)
            ->where('amount', '<', 0)
            ->sum('amount');

        $loyaltyDiscount = 0;
        if ($loyaltyNegativeSum < 0) {
            $loyaltyDiscount = -1 * $loyaltyNegativeSum;
        }

        $this->discount_total = $promoDiscount + $loyaltyDiscount;
        $this->total = max($subtotal - $this->discount_total, 0);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
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
