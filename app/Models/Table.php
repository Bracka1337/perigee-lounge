<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    use LogsActivity;

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
     * Finds a table from its QR code: the table name ("Z4", any case), its id ("4"),
     * or "z" followed by the id ("z4").
     */
    public static function findByQrCode(string $code): ?self
    {
        $byName = self::whereRaw('LOWER(name) = ?', [strtolower($code)])->first();
        if ($byName !== null) {
            return $byName;
        }

        $id = (strlen($code) > 1 && strtolower($code[0]) === 'z') ? substr($code, 1) : $code;

        return ctype_digit($id) ? self::find((int) $id) : null;
    }

    public function occupy(): void
    {
        $this->is_occupied = true;
        $this->save();
    }

    /**
     * Free the table unless another open order or a seated guest still holds it.
     */
    public function releaseIfFree(?int $exceptOrderId = null): void
    {
        $hasOpenOrder = Order::where('table_id', $this->id)
            ->whereNotIn('status', OrderStatus::closed())
            ->when($exceptOrderId !== null, fn ($query) => $query->where('id', '!=', $exceptOrderId))
            ->exists();

        $hasSeatedGuest = Reservation::where('table_id', $this->id)
            ->where('status', ReservationStatus::Active)
            ->exists();

        if (! $hasOpenOrder && ! $hasSeatedGuest) {
            $this->is_occupied = false;
            $this->save();
        }
    }

    /**
     * Получить бронирования
     *
     * @return HasMany<Reservation, $this>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
