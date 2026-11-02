<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'set.locale' => \App\Http\Middleware\SetLocale::class,
            'auth' => \App\Http\Middleware\Authenticate::class,
            'check.table.binding' => \App\Http\Middleware\CheckTableBinding::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $e) {
            $locale = request()->segment(1);
            $locales = config('site.locales', ['ru', 'en', 'lv']);
            if (!in_array($locale, $locales, true)) {
                $locale = config('site.default_locale', 'ru');
            }
            app()->setLocale($locale);
            
            return response()->view('errors.404', ['currentLocale' => $locale], 404);
        });

        $exceptions->render(function (AccessDeniedHttpException $e) {
            $locale = request()->segment(1);
            $locales = config('site.locales', ['ru', 'en', 'lv']);
            if (!in_array($locale, $locales, true)) {
                $locale = config('site.default_locale', 'ru');
            }
            app()->setLocale($locale);
            
            return response()->view('errors.403', ['currentLocale' => $locale], 403);
        });
    })->create();
