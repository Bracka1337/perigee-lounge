<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Validation\ValidationException;
use App\Models\Order;
use App\Models\Concerns\LogsActivity;

/**
 * @use HasFactory<\Database\Factories\ReservationFactory>
 */
class Reservation extends Model
{
    use HasFactory, LogsActivity;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

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
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    /**
     * @param \Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_ACTIVE,
        ]);
    }

    protected static function booted()
    {
        static::creating(function (Reservation $reservation) {
            $startDate = null;
            if ($reservation->start_at !== null && $reservation->start_at !== '') {
                try {
                    $startDate = \Carbon\Carbon::parse($reservation->start_at)->toDateString();
                } catch (\Exception $e) {
                    $startDate = null;
                }
            }

            $query = Order::where('table_id', $reservation->table_id)
                ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED]);

            if ($startDate !== null && $startDate !== '') {
                $query->whereDate('started_at', $startDate);
            }

            $hasActiveOrder = $query->exists();

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Reservation>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить столик
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Table, Reservation>
     */
    public function table(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Получить заказ
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<Order, Reservation>
     */
    public function order(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Order::class);
    }
}
