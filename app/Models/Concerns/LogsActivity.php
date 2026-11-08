<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            $model->logActivity('created', null, $model->getLoggableAttributes());
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();

            unset($changes['updated_at']);

            if (count($changes) > 0) {
                $old = [];
                foreach (array_keys($changes) as $key) {
                    $old[$key] = $model->getOriginal($key);
                }

                // Skip values that only changed type, e.g. "10.00" to 10.
                $actualChanges = [];
                $actualOld = [];
                foreach ($changes as $key => $newValue) {
                    $oldValue = $old[$key];
                    if (! $model->valuesAreEqual($oldValue, $newValue)) {
                        $actualChanges[$key] = $newValue;
                        $actualOld[$key] = $oldValue;
                    }
                }

                if (count($actualChanges) > 0) {
                    $model->logActivity('updated', $actualOld, $actualChanges);
                }
            }
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted', $model->getLoggableAttributes(), null);
        });
    }

    private function valuesAreEqual(mixed $oldValue, mixed $newValue): bool
    {
        $normalizedOld = $this->normalizeValue($oldValue);
        $normalizedNew = $this->normalizeValue($newValue);

        if (is_numeric($normalizedOld) && is_numeric($normalizedNew)) {
            return (float) $normalizedOld === (float) $normalizedNew;
        }

        return $normalizedOld === $normalizedNew;
    }

    /**
     * Cast numbers and numeric strings to float so "10.00" and 10 compare equal.
     */
    private function normalizeValue(mixed $value): mixed
    {
        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        if (is_numeric($value) && ! is_string($value)) {
            return (float) $value;
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    protected function logActivity(string $action, ?array $oldValues, ?array $newValues): void
    {
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
     * @return array<string, mixed>
     */
    protected function getLoggableAttributes(): array
    {
        $attributes = $this->getAttributes();

        $excluded = ['created_at', 'updated_at', 'deleted_at', 'password', 'remember_token'];

        return array_diff_key($attributes, array_flip($excluded));
    }

    /**
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
     * @return MorphMany<ActivityLog, $this>
     */
    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }
}
