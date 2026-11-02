<?php

namespace App\Models\Concerns;

trait HasTranslations
{
    public function t(string $field): ?string
    {
        $value = $this->{$field} ?? null;

        if (! is_array($value)) {
            return $value;
        }

        $locale = app()->getLocale();
        $fallback = config('app.fallback_locale');
        $default = config('site.default_locale', $fallback);

        return $value[$locale]
            ?? (($fallback !== null && $fallback !== '' && isset($value[$fallback])) ? $value[$fallback] : null)
            ?? (($default !== null && $default !== '' && isset($value[$default])) ? $value[$default] : null)
            ?? (count($value) > 0 ? reset($value) : null);
    }
}
