<?php

use App\Actions\Reservations\ExpireLateReservations;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('reservations:expire', function (ExpireLateReservations $expire) {
    $this->info($expire->handle().' reservations expired.');
})->purpose('Expire pending reservations the guest is more than an hour late for');

Schedule::command('reservations:expire')->everyFiveMinutes();

// Refresh the list of disposable email domains used by registration.
Schedule::command('disposable:update')->weekly();
