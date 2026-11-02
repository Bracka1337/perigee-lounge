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
    ];

    /**
     * @return BelongsTo<MenuCategory, MenuItem>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    /**
     * Получить элементы заказов
     *
     * @return HasMany<OrderItem, MenuItem>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format((float) $this->price, 2, '.', ' ').' €';
    }

    public function getImageUrlAttribute(): ?string
    {
        $path = $this->image_path ?? $this->image ?? null;

        return ($path !== null && $path !== '') ? Storage::url($path) : null;
    }
}
