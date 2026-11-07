<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Translation extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public static function translate(string $key, ?string $default = null, ?string $locale = null): string
    {
        return self::getValue($key, $locale) ?? $default ?? $key;
    }

    public static function forgetCache(string $key): void
    {
        Cache::forget("translation-values.{$key}");
    }

    public static function getValue(string $key, ?string $locale = null): ?string
    {
        $values = self::cachedValues($key);
        if ($values === null) {
            return null;
        }

        $locale = $locale ?? app()->getLocale();
        $locales = config('site.locales', ['ru']);
        $defaultLocale = config('site.default_locale', $locales[0] ?? 'ru');

        return $values[$locale] ?? $values[$defaultLocale] ?? null;
    }

    /**
     * The [locale => text] values for a key. Cached as a plain array, not a model,
     * so the cache never has to unserialize objects.
     *
     * @return array<string, string>|null
     */
    private static function cachedValues(string $key): ?array
    {
        return Cache::rememberForever(
            "translation-values.{$key}",
            fn () => self::where('key', $key)->first()?->value,
        );
    }
}
