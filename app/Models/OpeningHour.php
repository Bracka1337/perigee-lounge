<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @use HasFactory<\Database\Factories\OpeningHourFactory>
 */
class OpeningHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'weekday',
        'is_open',
        'opens_at',
        'closes_at',
    ];

    protected $casts = [
        'is_open' => 'boolean',
    ];
}
