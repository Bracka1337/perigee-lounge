<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Models\Concerns\LogsActivity;
use Carbon\Carbon;
use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Validation\ValidationException;

/**
 * @use HasFactory<ReservationFactory>
 */
class Reservation extends Model
{
    use HasFactory, LogsActivity;

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
            $startDate = null;
            if ($reservation->start_at !== null && $reservation->start_at !== '') {
                try {
                    $startDate = Carbon::parse($reservation->start_at)->toDateString();
                } catch (\Exception $e) {
                    $startDate = null;
                }
            }

            $query = Order::where('table_id', $reservation->table_id)
                ->whereNotIn('status', OrderStatus::closed());

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
     * @return BelongsTo<User, Reservation>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить столик
     *
     * @return BelongsTo<Table, Reservation>
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Получить заказ
     *
     * @return HasOne<Order, Reservation>
     */
    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }
}
