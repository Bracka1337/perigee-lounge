<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        $locale = $request->route('locale')
            ?? App::getLocale()
            ?? config('site.default_locale', config('app.locale', 'ru'));

        return route('login', ['locale' => $locale]);
    }
}
