<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Validation\ValidationException;

class Reservation extends Model
{
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'table_id',
        'guest_name',
        'guest_phone',
        'start_at',
        'end_at',
        'status',
        'guests_count',
        'source',
    ];

    protected $casts = [
        'status' => ReservationStatus::class,
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ReservationStatus::holdingTable());
    }

    protected static function booted()
    {
        static::creating(function (Reservation $reservation) {
            $hasActiveOrder = Order::where('table_id', $reservation->table_id)
                ->whereNotIn('status', OrderStatus::closed())
                ->whereDate('started_at', $reservation->start_at->toDateString())
                ->exists();

            if ($hasActiveOrder) {
                throw ValidationException::withMessages([
                    'table_id' => [__('client-reservations-error-overlap')],
                ]);
            }
        });
    }

    /**
     * Получить пользователя
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить столик
     *
     * @return BelongsTo<Table, $this>
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Получить заказ
     *
     * @return HasOne<Order, $this>
     */
    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }
}
