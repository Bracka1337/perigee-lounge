<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Client\PromoCodeController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientLoyaltyController;
use App\Http\Controllers\ClientOrderController;
use App\Http\Controllers\ClientReservationController;
use App\Http\Controllers\ClientTableOrderController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\MenuCategoryController;
use App\Http\Controllers\Staff\MenuItemController;
use App\Http\Controllers\Staff\MenuSortController;
use App\Http\Controllers\Staff\OrderController as StaffOrderController;
use App\Http\Controllers\Staff\OrderItemCatalogController;
use App\Http\Controllers\Staff\PromoCodeController as StaffPromoCodeController;
use App\Http\Controllers\Staff\ReservationController;
use App\Http\Controllers\Staff\StaffController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $locales = config('site.locales', ['ru']);
    $default = config('site.default_locale', $locales[0] ?? 'ru');

    return redirect()->route('home', ['locale' => $default]);
});

// Allow QR path without locale by redirecting to default locale
Route::get('/menu/table-{code}', function (string $code) {
    $locales = config('site.locales', ['ru']);
    $default = config('site.default_locale', $locales[0] ?? 'ru');

    // If user is not authenticated, redirect to regular menu
    if (! auth()->check()) {
        return redirect()->route('menu', ['locale' => $default]);
    }

    return redirect()->route('client.table.menu', ['locale' => $default, 'code' => $code]);
});

// Catch all routes without locale prefix and redirect to default locale
Route::fallback(function (Request $request) {
    $locales = config('site.locales', ['ru']);
    $default = config('site.default_locale', $locales[0] ?? 'ru');
    $path = $request->path();

    // Check if the path already starts with a valid locale
    $firstSegment = explode('/', $path)[0] ?? '';
    if (in_array($firstSegment, $locales, true)) {
        // Already has locale, this is a real 404
        abort(404);
    }

    // Redirect to the same path with default locale prefix
    return redirect("/{$default}/{$path}", 301);
});

Route::group([
    'prefix' => '{locale}',
    'middleware' => ['set.locale'],
    'where' => [
        'locale' => implode('|', config('site.locales', ['ru', 'en', 'lv'])),
    ],
], function () {

    Route::view('/', 'pages.home')->name('home');
    Route::get('/menu', [MenuController::class, 'index'])->name('menu');
    // QR menu for table, e.g. /menu/table-z4 (code=z4 or numeric id)
    Route::get('/menu/table-{code}', [ClientTableOrderController::class, 'show'])
        ->middleware(['check.table.binding'])
        ->name('client.table.menu');
    Route::view('/contacts', 'pages.contacts')->name('contacts');
    Route::view('/about', 'pages.about')->name('about');

    // Legal pages
    Route::view('/terms', 'pages.terms')->name('terms');
    Route::view('/privacy', 'pages.privacy')->name('privacy');
    Route::view('/cookies', 'pages.cookies')->name('cookies');

    Route::get('/dashboard', [ClientController::class, 'index'])
        ->middleware(['auth', 'verified'])
        ->name('dashboard');

    // QR table ordering - view and edit cart require auth
    Route::get('/menu/table-{code}/cart', [ClientTableOrderController::class, 'cart'])
        ->middleware(['check.table.binding'])
        ->name('client.table.cart.show');

    Route::middleware(['auth', 'check.table.binding'])->group(function () {
        Route::post('/menu/table-{code}/cart', [ClientTableOrderController::class, 'addToCart'])->name('client.table.cart.add');
        Route::post('/menu/table-{code}/cart/{itemId}', [ClientTableOrderController::class, 'updateCart'])->where('itemId', '[0-9]{1,9}')->name('client.table.cart.update');
        Route::delete('/menu/table-{code}/cart/{itemId}', [ClientTableOrderController::class, 'removeFromCart'])->where('itemId', '[0-9]{1,9}')->name('client.table.cart.remove');
        Route::post('/menu/table-{code}/confirm', [ClientTableOrderController::class, 'confirm'])->name('client.table.cart.confirm');
    });

    Route::middleware(['auth', 'role:client', 'verified'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/promo-codes', [PromoCodeController::class, 'index'])->name('client.promo-codes.index');
        Route::post('/promo-codes', [PromoCodeController::class, 'store'])->name('client.promo-codes.store');
        Route::get('/reservations', [ClientReservationController::class, 'index'])->name('client.reservations.index');
        Route::post('/reservations', [ClientReservationController::class, 'store'])->name('client.reservations.store');
        Route::post('/reservations/{reservation}/cancel', [ClientReservationController::class, 'cancel'])->name('client.reservations.cancel');

        Route::get('/orders', [ClientOrderController::class, 'index'])->name('client.orders.index');
        Route::get('/orders/{order}', [ClientOrderController::class, 'show'])->name('client.orders.show');
        Route::get('/loyalty', [ClientLoyaltyController::class, 'show'])->name('client.loyalty.show');
    });

    Route::middleware(['auth', 'role:admin', 'verified'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
            Route::resource('users', UserController::class);
            Route::resource('translations', TranslationController::class)->except(['show']);
            Route::resource('tables', TableController::class)->except(['show']);

            Route::post('tables/layout', [TableController::class, 'updateLayout'])
                ->name('tables.layout.update');

            Route::post('tables/opening-hours', [TableController::class, 'updateOpeningHours'])
                ->name('tables.opening-hours.update');

            Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');
        });

    Route::middleware(['auth', 'role:staff', 'verified'])
        ->prefix('staff')
        ->name('staff.')
        ->group(function () {
            Route::get('/dashboard', [StaffController::class, 'index'])->name('dashboard');
            Route::resource('menu-categories', MenuCategoryController::class)->except(['show']);
            Route::resource('menu-items', MenuItemController::class)->except(['show', 'index']);

            Route::post('/menu/sort', [MenuSortController::class, 'update'])->name('menu.sort');
            Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
            Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
            Route::patch('/reservations/{reservation}/arrive', [ReservationController::class, 'markArrived'])->name('reservations.arrive');
            Route::patch('/reservations/{reservation}/complete', [ReservationController::class, 'markCompleted'])->name('reservations.complete');
            Route::patch('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');

            Route::get('/orders', [StaffOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/create', [StaffOrderController::class, 'create'])->name('orders.create');
            Route::post('/orders', [StaffOrderController::class, 'store'])->name('orders.store');
            Route::post('/orders/from-reservation/{reservation}', [StaffOrderController::class, 'fromReservation'])->name('orders.from-reservation');
            Route::get('/orders/{order}', [StaffOrderController::class, 'show'])->name('orders.show');
            Route::post('/orders/{order}/items', [StaffOrderController::class, 'addItem'])->name('orders.items.store');
            Route::patch('/orders/{order}/items/{item}', [StaffOrderController::class, 'updateItem'])->name('orders.items.update');
            Route::patch('/orders/{order}/items/{item}/served', [StaffOrderController::class, 'markServed'])->name('orders.items.served');
            Route::delete('/orders/{order}/items/{item}', [StaffOrderController::class, 'destroyItem'])->name('orders.items.destroy');
            Route::post('/orders/{order}/attach-client', [StaffOrderController::class, 'attachClient'])->name('orders.attach-client');
            Route::post('/orders/{order}/apply-promo', [StaffOrderController::class, 'applyPromo'])->name('orders.apply-promo');
            Route::post('/orders/{order}/apply-loyalty', [StaffOrderController::class, 'applyLoyalty'])->name('orders.apply-loyalty');
            Route::post('/orders/{order}/complete', [StaffOrderController::class, 'complete'])->name('orders.complete');
            // Cancel endpoint removed per requirements: staff cannot cancel orders.

            Route::get('orders/{order}/items/catalog', [OrderItemCatalogController::class, 'index'])->name('orders.items.catalog');
            Route::post('orders/{order}/items', [OrderItemCatalogController::class, 'store'])->name('orders.items.store');

            Route::resource('promo-codes', StaffPromoCodeController::class)->except(['show']);
        });

    require __DIR__.'/auth.php';
});
