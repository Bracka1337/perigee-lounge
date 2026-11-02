<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locales = config('site.locales', ['ru']);
        $default = config('site.default_locale', $locales[0] ?? 'ru');

        $route    = $request->route();
        $locale   = $route?->parameter('locale') ?? $default;

        if (!in_array($locale, $locales, true)) {
            $locale = $default;
        }

        App::setLocale($locale);
        URL::defaults(['locale' => $locale]);

        // Update user's preferred locale if authenticated and different
        if (Auth::check() && Auth::user()->locale !== $locale) {
            Auth::user()->update(['locale' => $locale]);
        }

        view()->share('currentLocale', $locale);
        view()->share('availableLocales', $locales);

        return $next($request);
    }
}
