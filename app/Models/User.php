<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\VerifyEmailCustom;
use App\Notifications\ResetPasswordNotification;
use App\Models\Concerns\LogsActivity;

use Illuminate\Contracts\Translation\HasLocalePreference;

/**
 * @property string|null $phone
 * @use HasFactory<\Database\Factories\UserFactory>
 */
class User extends Authenticatable implements MustVerifyEmail, HasLocalePreference
{
    use HasFactory, Notifiable, LogsActivity;

    public const ROLE_CLIENT = 'client';
    public const ROLE_STAFF = 'staff';
    public const ROLE_ADMIN  = 'admin';

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
     * Получить заказы пользователя
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Order, User>
     */
    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Получить промокоды пользователя
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<PromoCode>
     */
    public function promoCodes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\PromoCode::class)
            ->withPivot(['claimed_at', 'used_at', 'is_used'])
            ->withTimestamps();
    }

    /**
     * Получить бонусы лояльности пользователя
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<LoyaltyEarning, User>
     */
    public function loyaltyEarnings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LoyaltyEarning::class);
    }

    public function sendEmailVerificationNotification()
    {
        try {
            $this->notify(new VerifyEmailCustom());
        } catch (\Exception $e) {
            throw new \Exception('Failed to send email verification notification: ' . $e->getMessage());
        }
    }

    public function sendPasswordResetNotification($token)
    {
        try {
            $this->notify(new ResetPasswordNotification($token));
        } catch (\Exception $e) {
            throw new \Exception('Failed to send password reset notification: ' . $e->getMessage());
        }
    }
}
