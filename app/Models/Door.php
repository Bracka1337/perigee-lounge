<?php

namespace App\Models;

use App\Enums\DoorKind;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A door on a wall line of a floor. x and y are its centre in metres; orientation "h" means
 * the wall runs along x, "v" along y. Every room wall under it gets an opening.
 */
class Door extends Model
{
    use LogsActivity;

    protected $fillable = [
        'floor_id',
        'kind',
        'orientation',
        'x',
        'y',
        'width',
    ];

    protected $casts = [
        'kind' => DoorKind::class,
        'x' => 'float',
        'y' => 'float',
        'width' => 'float',
    ];

    /**
     * @return BelongsTo<Floor, $this>
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }
}
