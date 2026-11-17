<?php

namespace App\Models;

use App\Enums\RoomColor;
use App\Enums\RoomKind;
use App\Enums\RoomMood;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A rectangle of a floor. x, y, width and height are in metres, measured from the
 * top-left corner of the building.
 */
class Room extends Model
{
    use HasTranslations, LogsActivity;

    protected $fillable = [
        'floor_id',
        'name',
        'kind',
        'color',
        'mood',
        'is_bookable_whole',
        'x',
        'y',
        'width',
        'height',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'kind' => RoomKind::class,
        'is_bookable_whole' => 'boolean',
        'color' => RoomColor::class,
        'mood' => RoomMood::class,
        'x' => 'float',
        'y' => 'float',
        'width' => 'float',
        'height' => 'float',
    ];

    /**
     * Whether a point of the floor, in metres, lies inside this room.
     */
    public function contains(float $x, float $y): bool
    {
        return $x >= $this->x && $x <= $this->x + $this->width
            && $y >= $this->y && $y <= $this->y + $this->height;
    }

    /**
     * @return BelongsTo<Floor, $this>
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    /**
     * @return HasMany<Table, $this>
     */
    public function tables(): HasMany
    {
        return $this->hasMany(Table::class);
    }
}
