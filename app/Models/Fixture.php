<?php

namespace App\Models;

use App\Enums\FixtureType;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Something placed in a room: a screen, a game station, a bar counter, a plant. x and y are its
 * centre in metres, width and depth its footprint in metres. A bar counter keeps its shape in
 * `points`, a list of [x, y] corners in metres.
 *
 * @property list<array{0: float, 1: float}>|null $points
 * @property array<string, mixed>|null $settings
 */
class Fixture extends Model
{
    use LogsActivity;

    protected $fillable = [
        'floor_id',
        'type',
        'x',
        'y',
        'rotation',
        'width',
        'depth',
        'points',
        'settings',
    ];

    protected $casts = [
        'type' => FixtureType::class,
        'x' => 'float',
        'y' => 'float',
        'width' => 'float',
        'depth' => 'float',
        'points' => 'array',
        'settings' => 'array',
    ];

    /**
     * @return BelongsTo<Floor, $this>
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }
}
