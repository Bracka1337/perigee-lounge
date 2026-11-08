<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * @property string|null $image
 */
class MenuItem extends Model
{
    use HasTranslations, LogsActivity;

    protected $fillable = [
        'menu_category_id',
        'name',
        'description',
        'allergens',
        'image_path',
        'price',
        'sort_order',
        'is_available',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'allergens' => 'array',
        'is_available' => 'boolean',
        'price' => 'float',
    ];

    /**
     * @return BelongsTo<MenuCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isOrderable(): bool
    {
        return ($this->is_available ?? true) && ($this->category->is_available ?? true);
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2, '.', ' ').' €';
    }

    public function getImageUrlAttribute(): ?string
    {
        $path = $this->image_path ?? $this->image ?? null;

        return ($path !== null && $path !== '') ? Storage::url($path) : null;
    }
}
