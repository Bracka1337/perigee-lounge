<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Panel • PERIGEE</title>
    <link rel="icon" type="image/png" href="{{ asset('/favicon-96x96.png') }}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('/favicon.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('/favicon.ico') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('/apple-touch-icon.png') }}" />
    <meta name="apple-mobile-web-app-title" content="Perigee" />
    <link rel="manifest" href="{{ asset('/site.webmanifest') }}" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-black text-white">

<div class="min-h-screen flex flex-col">

    @include('staff.partials.header')

    <main class="flex-1 pt-20 pb-10">
        <div class="max-w-6xl mx-auto px-6">
            @yield('content')
        </div>
    </main>

    @include('staff.partials.footer')

</div>

@stack('scripts')
@stack('styles')
</body>
</html>
