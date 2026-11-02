<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @use HasFactory<\Database\Factories\PromoCodeUsageFactory>
 */
class PromoCodeUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'promo_code_id',
        'order_id',
        'user_id',
        'discount_amount',
    ];

    /**
     * @return BelongsTo<PromoCode, PromoCodeUsage>
     */
    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    /**
     * @return BelongsTo<Order, PromoCodeUsage>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<User, PromoCodeUsage>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
