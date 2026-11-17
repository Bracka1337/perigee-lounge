<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Floor extends Model
{
    use HasTranslations, LogsActivity;

    protected $fillable = [
        'name',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
    ];

    /**
     * @return HasMany<Room, $this>
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<Door, $this>
     */
    public function doors(): HasMany
    {
        return $this->hasMany(Door::class)->orderBy('id');
    }

    /**
     * @return HasMany<Fixture, $this>
     */
    public function fixtures(): HasMany
    {
        return $this->hasMany(Fixture::class)->orderBy('id');
    }

    /**
     * @return HasManyThrough<Table, Room, $this>
     */
    public function tables(): HasManyThrough
    {
        return $this->hasManyThrough(Table::class, Room::class);
    }
}
