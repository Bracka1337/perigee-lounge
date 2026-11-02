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
     * Получить бронирования
     *
     * @return HasMany<Reservation, Table>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
