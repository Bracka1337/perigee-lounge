<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'model_type',
        'model_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Получить пользователя, который совершил действие
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить модель, с которой произошло действие
     *
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo('model');
    }

    /**
     * Получить читабельное название действия
     */
    public function getActionNameAttribute(): string
    {
        return match ($this->action) {
            'created' => __('logs-action-created'),
            'updated' => __('logs-action-updated'),
            'deleted' => __('logs-action-deleted'),
            default => $this->action,
        };
    }

    /**
     * Получить читабельное название модели
     */
    public function getModelNameAttribute(): string
    {
        return self::modelLabel($this->model_type);
    }

    public static function modelLabel(?string $type): string
    {
        $key = match ($type) {
            'App\\Models\\Order' => 'logs-model-order',
            'App\\Models\\MenuItem' => 'logs-model-menuitem',
            'App\\Models\\MenuCategory' => 'logs-model-menucategory',
            'App\\Models\\Reservation' => 'logs-model-reservation',
            'App\\Models\\Table' => 'logs-model-table',
            'App\\Models\\User' => 'logs-model-user',
            'App\\Models\\PromoCode' => 'logs-model-promocode',
            default => null,
        };

        return $key !== null ? __($key) : class_basename($type ?? '');
    }

    /**
     * Получить изменения в виде массива с описанием
     *
     * @return array<string, mixed>
     */
    public function getChangesAttribute(): array
    {
        if ($this->action === 'created') {
            return $this->new_values ?? [];
        }

        if ($this->action === 'deleted') {
            return $this->old_values ?? [];
        }

        // Для updated - показываем только измененные поля
        $changes = [];
        $oldValues = $this->old_values ?? [];
        $newValues = $this->new_values ?? [];

        foreach ($newValues as $key => $newValue) {
            $oldValue = $oldValues[$key] ?? null;

            // Нормализуем числовые значения перед сравнением
            $normalizedOld = $this->normalizeValue($oldValue);
            $normalizedNew = $this->normalizeValue($newValue);

            if ($normalizedOld !== $normalizedNew) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    /**
     * Нормализовать значение для сравнения (числа, строки и т.д.)
     */
    private function normalizeValue(mixed $value): mixed
    {
        // Если это числовая строка, преобразуем в float для корректного сравнения
        if (is_string($value) && is_numeric($value)) {
            $floatValue = (float) $value;
            // Проверяем, есть ли дробная часть
            if (strpos($value, '.') !== false) {
                return $floatValue;
            }

            return (int) $floatValue;
        }

        // Если уже число, просто возвращаем
        if (is_numeric($value) && ! is_string($value)) {
            return $value;
        }

        return $value;
    }
}
