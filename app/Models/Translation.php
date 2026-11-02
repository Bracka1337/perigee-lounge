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
        $locale        = $locale ?? app()->getLocale();
        $locales       = config('site.locales', ['ru']);
        $defaultLocale = config('site.default_locale', $locales[0] ?? 'ru');

        $item = Cache::rememberForever("translation.{$key}", function () use ($key) {
            return self::where('key', $key)->first();
        });

        if ($item === null) {
            return $default ?? $key;
        }

        $values = $item->value ?? [];

        return $values[$locale]
            ?? $values[$defaultLocale]
            ?? $default
            ?? $key;
    }

    public static function forgetCache(string $key): void
    {
        Cache::forget("translation.{$key}");
    }

    public static function getValue(string $key, ?string $locale = null): ?string
    {
        $locale        = $locale ?? app()->getLocale();
        $locales       = config('site.locales', ['ru']);
        $defaultLocale = config('site.default_locale', $locales[0] ?? 'ru');

        $item = Cache::rememberForever("translation.{$key}", function () use ($key) {
            return self::where('key', $key)->first();
        });

        if ($item === null) {
            return null;
        }

        $values = $item->value ?? [];

        return $values[$locale]
            ?? $values[$defaultLocale]
            ?? null;
    }

}
