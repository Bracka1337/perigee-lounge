<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Database\Factories\TableFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @use HasFactory<TableFactory>
 */
class Table extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'capacity',
        'pos_x',
        'pos_y',
        'width',
        'height',
        'rotation',
        'is_active',
        'is_occupied',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_occupied' => 'boolean',
    ];

    /**
     * Free the table unless another open order or a seated guest still holds it.
     */
    public function releaseIfFree(?int $exceptOrderId = null): void
    {
        $hasOpenOrder = Order::where('table_id', $this->id)
            ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->when($exceptOrderId !== null, fn ($query) => $query->where('id', '!=', $exceptOrderId))
            ->exists();

        $hasSeatedGuest = Reservation::where('table_id', $this->id)
            ->where('status', Reservation::STATUS_ACTIVE)
            ->exists();

        if (! $hasOpenOrder && ! $hasSeatedGuest) {
            $this->is_occupied = false;
            $this->save();
        }
    }

    /**
     * Получить бронирования
     *
     * @return HasMany<Reservation, Table>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
