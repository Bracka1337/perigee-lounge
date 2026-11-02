<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\LogsActivity;

/**
 * @use HasFactory<\Database\Factories\TableFactory>
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Reservation, Table>
     */
    public function reservations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
