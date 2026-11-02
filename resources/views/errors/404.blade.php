@php
    $locales = config('site.locales', ['ru', 'en', 'lv']);
    $default = config('site.default_locale', 'ru');
    $locale = $currentLocale ?? request()->segment(1);
    
    if (!in_array($locale, $locales, true)) {
        $locale = $default;
    }
    
    app()->setLocale($locale);
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - {{ __('perigee-rest-name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .logo-font { font-family: 'Cormorant Garamond', serif; font-weight: 300; }
    </style>
</head>
<body class="bg-black text-white antialiased">
    <div class="w-full min-h-screen">
        <section class="relative min-h-screen flex items-center justify-center overflow-hidden">
            <div class="absolute inset-0 hero-bg"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/80 to-black"></div>

            <div class="relative z-10 px-6 max-w-3xl mx-auto text-center space-y-6">
                <p class="text-xs tracking-[0.35em] uppercase text-white/60">
                    {{ __('perigee-rest-name') }}
                </p>

                <h1 class="logo-font text-5xl md:text-6xl drop-shadow-[0_0_10px_rgba(255,255,255,0.7)]">
                    404
                </h1>

                <p class="text-sm md:text-base text-white/70">
                    {{ __('404-title') }}
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-6">
                    <a href="{{ route('home', ['locale' => $locale]) }}"
                       class="px-8 py-3 rounded-full border border-white/80 text-xs md:text-sm uppercase tracking-[0.25em]
                          drop-shadow-[0_0_10px_rgba(255,255,255,0.7)] hover:bg-white/10 transition">
                        {{ __('to-main-button') }}
                    </a>

                    <a href="{{ route('menu', ['locale' => $locale]) }}"
                       class="px-8 py-3 rounded-full border border-white/30 text-xs md:text-sm uppercase tracking-[0.25em]
                          text-white/80 hover:text-white hover:border-white/70 hover:bg-white/5 transition">
                        {{ __('perigee-menu-button') }}
                    </a>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
