<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Database\Factories\PromoCodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @use HasFactory<PromoCodeFactory>
 */
class PromoCode extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'public_code',
        'description',
        'qr_code',
        'discount_type',
        'discount_value',
        'max_uses',
        'used_count',
        'claimed_count',
        'valid_from',
        'valid_until',
        'scope',
        'menu_category_id',
        'menu_item_id',
        'is_active',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * @return HasMany<PromoCodeUsage, PromoCode>
     */
    public function usages(): HasMany
    {
        return $this->hasMany(PromoCodeUsage::class);
    }

    /**
     * @return BelongsTo<MenuCategory, PromoCode>
     */
    public function menuCategory(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    /**
     * @return BelongsTo<MenuItem, PromoCode>
     */
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }

    /**
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['claimed_at', 'used_at', 'is_used', 'is_redeemed', 'redeemed_at'])
            ->withTimestamps();
    }

    public function isAvailable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = Carbon::now();

        if ($this->valid_from !== null && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until !== null && $now->gt($this->valid_until)) {
            return false;
        }

        if (! is_null($this->max_uses) && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function isClaimable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = Carbon::now();

        if ($this->valid_from !== null && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until !== null && $now->gt($this->valid_until)) {
            return false;
        }

        if (! is_null($this->max_uses) && $this->claimed_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function isClaimedBy(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function claimForUser(User $user, bool $markUsed = false): bool
    {
        if (! $this->isClaimable()) {
            return false;
        }

        if ($this->isClaimedBy($user)) {
            return false;
        }

        $pivotData = [
            'claimed_at' => Carbon::now(),
            'is_used' => $markUsed ? true : false,
        ];

        if ($markUsed) {
            $pivotData['used_at'] = Carbon::now();
        }

        $this->users()->attach($user->id, $pivotData);

        // increment claimed count always when user claims
        $this->claimed_count = ($this->claimed_count ?? 0) + 1;

        // if marking as used (client added counts as used), increment used_count too
        if ($markUsed) {
            $this->used_count = ($this->used_count ?? 0) + 1;
        }

        $this->save();

        return true;
    }

    public function calculateDiscountForOrder(Order $order): float
    {
        $order->loadMissing('items.menuItem');
        $subtotal = 0;
        $eligible = 0;

        foreach ($order->items as $item) {
            $subtotal += $item->total_price;
            $isEligible = false;

            if ($this->scope === 'all') {
                $isEligible = true;
            } elseif ($this->scope === 'item' && $this->menu_item_id === $item->menu_item_id) {
                $isEligible = true;
            } elseif ($this->scope === 'category' && $item->menuItem !== null && $item->menuItem->menu_category_id === $this->menu_category_id) {
                $isEligible = true;
            }

            if ($isEligible) {
                $eligible += $item->total_price;
            }
        }

        if ($eligible <= 0) {
            return 0;
        }

        if ($this->discount_type === 'percent') {
            $discount = $eligible * ($this->discount_value / 100);
        } else {
            $discount = $this->discount_value;
            if ($discount > $eligible) {
                $discount = $eligible;
            }
        }

        return round($discount, 2);
    }

    public function getDiscountDetailsForOrder(Order $order): array
    {
        $order->loadMissing('items.menuItem.category');
        $eligible = 0;
        $eligibleItems = [];

        foreach ($order->items as $item) {
            $isEligible = false;

            if ($this->scope === 'all') {
                $isEligible = true;
            } elseif ($this->scope === 'item' && $this->menu_item_id === $item->menu_item_id) {
                $isEligible = true;
            } elseif ($this->scope === 'category' && $item->menuItem !== null && $item->menuItem->menu_category_id === $this->menu_category_id) {
                $isEligible = true;
            }

            if ($isEligible) {
                $eligible += $item->total_price;
                $eligibleItems[] = $item;
            }
        }

        $discount = 0;
        if ($eligible > 0) {
            if ($this->discount_type === 'percent') {
                $discount = $eligible * ($this->discount_value / 100);
            } else {
                $discount = $this->discount_value;
                if ($discount > $eligible) {
                    $discount = $eligible;
                }
            }
            $discount = round($discount, 2);
        }

        return [
            'scope' => $this->scope,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'eligible_amount' => round($eligible, 2),
            'discount_amount' => $discount,
            'eligible_items' => $eligibleItems,
            'category_name' => $this->menuCategory !== null ? $this->menuCategory->name : null,
            'item_name' => $this->menuItem !== null ? $this->menuItem->name : null,
        ];
    }
}
