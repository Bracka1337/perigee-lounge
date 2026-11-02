<!DOCTYPE html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    
    @php
        use Illuminate\Support\Facades\Lang;

        $seoKey = trim($__env->yieldContent('seo_key', 'default'));

        $title = Lang::has("seo.$seoKey.title")
            ? __("seo.$seoKey.title")
            : __("seo.default.title");

        $description = Lang::has("seo.$seoKey.description")
            ? __("seo.$seoKey.description")
            : __("seo.default.description");

        $keywords = Lang::has("seo.$seoKey.keywords")
            ? __("seo.$seoKey.keywords")
            : __("seo.default.keywords");

        $image = asset('images/logo.png');
        $url = url()->current();

        $locales = config('site.locales', ['ru','en','lv']);
        $defaultLocale = config('site.default_locale', 'ru');
        $currentLocale = app()->getLocale();
        $segments = request()->segments();
        $relativePath = implode('/', array_slice($segments, 1));
        $altUrls = collect($locales)->mapWithKeys(function ($loc) use ($relativePath) {
            $path = $loc . ($relativePath ? '/' . $relativePath : '');
            return [$loc => url($path)];
        });
        $ogLocaleMap = ['ru' => 'ru_RU', 'en' => 'en_US', 'lv' => 'lv_LV'];
        $ogLocale = $ogLocaleMap[$currentLocale] ?? 'en_US';
    @endphp


    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="keywords" content="{{ $keywords }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:image" content="{{ $image }}">
    <meta property="og:image:secure_url" content="{{ $image }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <meta property="og:locale" content="{{ $ogLocale }}">
    @foreach($locales as $loc)
        @if($loc !== $currentLocale)
            <meta property="og:locale:alternate" content="{{ $ogLocaleMap[$loc] ?? 'en_US' }}">
        @endif
    @endforeach

    <link rel="canonical" href="{{ $url }}" />
    @foreach($locales as $loc)
        <link rel="alternate" hreflang="{{ $loc }}" href="{{ $altUrls[$loc] }}" />
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $altUrls[$defaultLocale] ?? $url }}" />

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $image }}">


    <!-- Favicon для различных устройств -->
    <link rel="icon" type="image/png" href="{{ asset('/favicon-96x96.png') }}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('/favicon.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('/favicon.ico') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('/apple-touch-icon.png') }}" />
    <meta name="apple-mobile-web-app-title" content="Perigee" />
    <link rel="manifest" href="{{ asset('/site.webmanifest') }}" />
    
    <!-- Дополнительные метатеги для поисковых систем -->
    <meta name="msapplication-TileColor" content="#07080F" />
    <meta name="theme-color" content="#07080F" />
    
    <!-- Schema.org для Google -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Restaurant",
        "name": "{{ config('app.name') }}",
        "image": "{{ $image }}",
        "logo": "{{ asset('images/logo.png') }}",
        "url": "{{ config('app.url') }}",
        "description": "{{ $description }}"
    }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased">


@include('partials.header')

<div>
    @yield('content')
</div>

@include('partials.footer')

@stack('scripts')
@stack('styles')

</body>
</html>
