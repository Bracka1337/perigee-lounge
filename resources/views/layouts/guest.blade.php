<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="flex min-h-screen flex-col items-center px-6 pt-6 sm:justify-center sm:pt-0">
            <a href="{{ route('home', ['locale' => app()->getLocale()]) }}">
                <img src="{{ asset('images/logo.svg') }}" alt="{{ config('app.name') }}" class="h-16 w-16">
            </a>

            <div class="mt-6 w-full rounded-2xl border border-line bg-surface p-6 sm:max-w-md">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
