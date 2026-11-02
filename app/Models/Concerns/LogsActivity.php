<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * Boot trait
     */
    protected static function bootLogsActivity(): void
    {
        // Логирование создания
        static::created(function ($model) {
            $model->logActivity('created', null, $model->getLoggableAttributes());
        });

        // Логирование обновления
        static::updated(function ($model) {
            $changes = $model->getChanges();

            // Игнорируем updated_at
            unset($changes['updated_at']);

            if (count($changes) > 0) {
                $old = [];
                foreach (array_keys($changes) as $key) {
                    $old[$key] = $model->getOriginal($key);
                }

                // Фильтруем только реально измененные значения (с нормализацией чисел)
                $actualChanges = [];
                $actualOld = [];
                foreach ($changes as $key => $newValue) {
                    $oldValue = $old[$key];
                    if (! $model->valuesAreEqual($oldValue, $newValue)) {
                        $actualChanges[$key] = $newValue;
                        $actualOld[$key] = $oldValue;
                    }
                }

                // Логируем только если есть реальные изменения
                if (count($actualChanges) > 0) {
                    $model->logActivity('updated', $actualOld, $actualChanges);
                }
            }
        });

        // Логирование удаления
        static::deleted(function ($model) {
            $model->logActivity('deleted', $model->getLoggableAttributes(), null);
        });
    }

    /**
     * Проверить, равны ли два значения с нормализацией чисел
     */
    private function valuesAreEqual(mixed $oldValue, mixed $newValue): bool
    {
        // Нормализуем оба значения
        $normalizedOld = $this->normalizeValue($oldValue);
        $normalizedNew = $this->normalizeValue($newValue);

        // Если оба числа, сравниваем численно
        if (is_numeric($normalizedOld) && is_numeric($normalizedNew)) {
            return (float) $normalizedOld === (float) $normalizedNew;
        }

        return $normalizedOld === $normalizedNew;
    }

    /**
     * Нормализовать значение для сравнения (числа, строки и т.д.)
     */
    private function normalizeValue(mixed $value): mixed
    {
        // Если это числовая строка, преобразуем в float для корректного сравнения
        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        // Если это число (int или float), преобразуем в float
        if (is_numeric($value) && ! is_string($value)) {
            return (float) $value;
        }

        return $value;
    }

    /**
     * Логировать активность
     *
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    protected function logActivity(string $action, ?array $oldValues, ?array $newValues): void
    {
        // Фильтруем чувствительные данные
        $oldValues = $this->filterSensitiveData($oldValues);
        $newValues = $this->filterSensitiveData($newValues);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Получить атрибуты для логирования
     *
     * @return array<string, mixed>
     */
    protected function getLoggableAttributes(): array
    {
        $attributes = $this->getAttributes();

        // Исключаем технические поля
        $excluded = ['created_at', 'updated_at', 'deleted_at', 'password', 'remember_token'];

        return array_diff_key($attributes, array_flip($excluded));
    }

    /**
     * Фильтровать чувствительные данные
     *
     * @param  array<string, mixed>|null  $data
     * @return array<string, mixed>|null
     */
    protected function filterSensitiveData(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        $sensitive = ['password', 'password_confirmation', 'remember_token', 'api_token'];

        foreach ($sensitive as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[HIDDEN]';
            }
        }

        return $data;
    }

    /**
     * Получить логи активности для данной модели
     *
     * @return MorphMany<ActivityLog, Model>
     */
    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }
}
