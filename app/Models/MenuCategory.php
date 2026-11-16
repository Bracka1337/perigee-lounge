<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuCategory extends Model
{
    use HasTranslations, LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'is_special',
        'is_available',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'is_special' => 'boolean',
        'is_available' => 'boolean',
    ];

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Categories with their items for the guest menus. With $availableOnly, items that can't be
     * ordered are left out, but every category stays so a chosen category anchor still exists.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForMenu($query, bool $availableOnly = false)
    {
        return $query->orderBy('sort_order')->with(['items' => function ($items) use ($availableOnly) {
            $items->orderBy('sort_order')->when($availableOnly, fn ($q) => $q
                ->where('is_available', true)
                ->whereHas('category', fn ($category) => $category->where('is_available', true)));
        }]);
    }
}
