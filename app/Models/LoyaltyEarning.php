<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @use HasFactory<\Database\Factories\LoyaltyEarningFactory>
 */
class LoyaltyEarning extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'amount',
    ];

    /**
     * @return BelongsTo<User, LoyaltyEarning>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Order, LoyaltyEarning>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
