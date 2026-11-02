<?php

namespace App\Models;

use Database\Factories\OpeningHourFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @use HasFactory<OpeningHourFactory>
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
