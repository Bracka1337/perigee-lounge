@php
    $locales = config('site.locales', ['ru', 'en', 'lv']);
    $locale = request()->segment(1);

    if (! in_array($locale, $locales, true)) {
        $locale = config('site.default_locale', 'ru');
    }

    app()->setLocale($locale);
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>@yield('code') - {{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <main class="relative flex min-h-screen items-center overflow-hidden">
        <svg aria-hidden="true" viewBox="0 0 900 620" class="pointer-events-none absolute -right-44 top-10 h-auto w-[900px] max-w-[110%]">
            <ellipse cx="450" cy="310" rx="400" ry="210" fill="none" stroke="#3A3D57" transform="rotate(-18 450 310)"/>
            <ellipse cx="450" cy="310" rx="290" ry="140" fill="none" stroke="#1E2033" transform="rotate(-18 450 310)"/>
            <circle cx="450" cy="310" r="74" fill="#0E0F1B" stroke="#3A3D57"/>
            <circle cx="94" cy="424" r="7" fill="#B9A5FF"/>
        </svg>

        <div class="relative mx-auto w-full max-w-7xl px-6 py-16">
            <a href="{{ route('home', ['locale' => $locale]) }}" class="mb-12 flex min-h-11 w-fit items-center gap-3">
                <img src="{{ asset('images/logo.svg') }}" alt="" class="h-8 w-8">
                <span class="font-display text-lg font-medium tracking-wide">perigee</span>
            </a>

            <p class="mb-4 text-xs font-bold uppercase tracking-[0.22em] text-nebula">@yield('code')</p>
            <h1 class="mb-10 max-w-2xl font-display text-[clamp(1.5rem,3vw,2.25rem)] font-light leading-tight tracking-tight">@yield('title')</h1>

            <div class="flex flex-wrap gap-3">
                @yield('actions')
            </div>
        </div>
    </main>
</body>
</html>
