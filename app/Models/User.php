<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailCustom;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property string|null $phone
 */
class User extends Authenticatable implements HasLocalePreference, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, LogsActivity, Notifiable;

    public const ROLE_CLIENT = 'client';

    public const ROLE_STAFF = 'staff';

    public const ROLE_ADMIN = 'admin';

    /**
     * Same defaults as the database columns, so new models have them before a refresh.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'locale' => 'ru',
        'loyalty_code' => null,
        'loyalty_balance' => 0,
    ];

    protected $fillable = [
        'name',
        'email',
        'birth_date',
        'locale',
        'password',
        'role',
        'loyalty_code',
        'loyalty_balance',
        'terms_agreed',
        'terms_agreed_at',
        'newsletter_agreed',
        'newsletter_agreed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'date',
            'terms_agreed_at' => 'datetime',
            'terms_agreed' => 'boolean',
            'newsletter_agreed_at' => 'datetime',
            'newsletter_agreed' => 'boolean',
            'password' => 'hashed',
            'loyalty_balance' => 'float',
        ];
    }

    /**
     * Get the user's preferred locale.
     */
    public function preferredLocale(): ?string
    {
        return $this->locale;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isWorker(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function isClient(): bool
    {
        return $this->role === self::ROLE_CLIENT;
    }

    /**
     * Name of another table this user already has an open order at, if any.
     * A client can only order at one table at a time.
     */
    public function tableBoundOtherThan(int $tableId): ?string
    {
        $order = $this->orders()->active()->first();

        if ($order === null || $order->table_id === $tableId) {
            return null;
        }

        return Table::whereKey($order->table_id)->value('name') ?? "#{$order->table_id}";
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return BelongsToMany<PromoCode, $this>
     */
    public function promoCodes(): BelongsToMany
    {
        return $this->belongsToMany(PromoCode::class)
            ->withPivot(['claimed_at', 'used_at', 'is_used'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<LoyaltyEarning, $this>
     */
    public function loyaltyEarnings(): HasMany
    {
        return $this->hasMany(LoyaltyEarning::class);
    }

    public function sendEmailVerificationNotification()
    {
        try {
            $this->notify(new VerifyEmailCustom);
        } catch (\Exception $e) {
            throw new \Exception('Failed to send email verification notification: '.$e->getMessage());
        }
    }

    public function sendPasswordResetNotification($token)
    {
        try {
            $this->notify(new ResetPasswordNotification($token));
        } catch (\Exception $e) {
            throw new \Exception('Failed to send password reset notification: '.$e->getMessage());
        }
    }
}
