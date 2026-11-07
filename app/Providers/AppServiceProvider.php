<?php

namespace App\Providers;

use App\Models\User;
use App\Translation\DatabaseTranslator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Propaganistas\LaravelDisposableEmail\Validation\Indisposable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->extend('translator', function ($translator, $app) {
            return new DatabaseTranslator(
                $translator->getLoader(),
                $translator->getLocale()
            );
        });
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Area access by role, used as `can:admin`, `can:staff` and `can:client` on route groups.
        Gate::define('admin', fn (User $user) => $user->isAdmin());
        Gate::define('staff', fn (User $user) => $user->isWorker());
        Gate::define('client', fn (User $user) => $user->isClient());

        // Переопределяем валидатор indisposable с переводом
        Validator::extend('indisposable', Indisposable::class.'@validate', '');
        Validator::replacer('indisposable', function ($message, $attribute, $rule, $parameters) {
            return __('validation.indisposable', ['attribute' => $attribute]);
        });
    }
}
