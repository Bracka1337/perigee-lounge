@php
    $availableLocales = config('site.locales', ['ru']);
    $currentLocale = app()->getLocale();
    $route = request()->route();
    $routeName = $route?->getName();
    $routeParams = $route?->parameters() ?? [];
    unset($routeParams['locale'], $routeParams['view'], $routeParams['status']);

    $localeUrl = fn (string $loc) => $routeName
        ? route($routeName, array_merge(['locale' => $loc], $routeParams))
        : route('home', ['locale' => $loc]);

    $navLinks = [
        ['route' => 'home', 'label' => __('header-home')],
        ['route' => 'about', 'label' => __('header-about')],
        ['route' => 'menu', 'label' => __('header-menu')],
        ['route' => 'contacts', 'label' => __('header-contacts')],
    ];

    $accountRoute = null;
    $accountLabel = null;

    if (auth()->check()) {
        [$accountRoute, $accountLabel] = match (true) {
            auth()->user()->isAdmin() => ['admin.dashboard', __('header-admin')],
            auth()->user()->isWorker() => ['staff.dashboard', __('header-staff')],
            default => ['dashboard', __('header-client')],
        };
    }
@endphp

<header id="siteHeader" class="fixed top-0 left-0 z-50 w-full border-b border-line bg-ground/90 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-3 sm:px-6">
        <a href="{{ route('home', ['locale' => $currentLocale]) }}" class="flex min-h-11 items-center gap-3">
            <img src="{{ asset('images/logo.svg') }}" alt="" class="h-8 w-8">
            <span class="font-display text-lg font-medium tracking-wide">perigee</span>
        </a>

        <nav class="hidden items-center gap-7 text-sm lg:flex">
            @foreach ($navLinks as $link)
                <a href="{{ route($link['route'], ['locale' => $currentLocale]) }}"
                   @if ($routeName === $link['route']) aria-current="page" @endif
                   class="flex min-h-11 items-center transition hover:text-ink {{ $routeName === $link['route'] ? 'text-ink' : 'text-ink-muted' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach

            <div class="flex items-center gap-2 text-xs text-ink-muted">
                @foreach ($availableLocales as $loc)
                    <a href="{{ $localeUrl($loc) }}" lang="{{ $loc }}"
                       @if ($loc === $currentLocale) aria-current="true" @endif
                       class="flex min-h-11 min-w-11 items-center justify-center transition hover:text-ink {{ $loc === $currentLocale ? 'font-bold text-ink' : '' }}">
                        {{ strtoupper($loc) }}
                    </a>
                @endforeach
            </div>

            @auth
                <a href="{{ route($accountRoute, ['locale' => $currentLocale]) }}" class="flex min-h-11 items-center text-ink-muted transition hover:text-ink">{{ $accountLabel }}</a>
                <form method="POST" action="{{ route('logout', ['locale' => $currentLocale]) }}">
                    @csrf
                    <button type="submit" class="min-h-11 rounded-full border border-line-strong px-5 transition hover:border-ink">
                        {{ __('header-logout') }}
                    </button>
                </form>
            @endauth

            @guest
                <a href="{{ route('login', ['locale' => $currentLocale]) }}"
                   class="flex min-h-11 items-center rounded-full border border-line-strong px-5 transition hover:border-ink">
                    {{ __('header-login') }}
                </a>
            @endguest
        </nav>

        <button id="navToggle" type="button" aria-controls="navMenu" aria-expanded="false" aria-label="{{ __('header-menu') }}"
                class="flex h-11 w-11 items-center justify-center rounded-full border border-line-strong lg:hidden">
            <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
                <path d="M3 6h14M3 14h14"/>
            </svg>
        </button>
    </div>

    <div id="navMenu" class="hidden max-h-[calc(100svh-68px)] overflow-y-auto border-t border-line bg-ground lg:hidden">
        <nav class="flex flex-col gap-1 px-4 py-4 sm:px-6">
            @foreach ($navLinks as $link)
                <a href="{{ route($link['route'], ['locale' => $currentLocale]) }}"
                   class="flex min-h-11 items-center {{ $routeName === $link['route'] ? 'text-ink' : 'text-ink-soft' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach

            <div class="flex items-center gap-3 text-sm text-ink-muted">
                @foreach ($availableLocales as $loc)
                    <a href="{{ $localeUrl($loc) }}" lang="{{ $loc }}"
                       class="flex min-h-11 min-w-11 items-center {{ $loc === $currentLocale ? 'font-bold text-ink' : '' }}">
                        {{ strtoupper($loc) }}
                    </a>
                @endforeach
            </div>

            @auth
                <a href="{{ route($accountRoute, ['locale' => $currentLocale]) }}" class="flex min-h-11 items-center text-ink-soft">{{ $accountLabel }}</a>
                <form method="POST" action="{{ route('logout', ['locale' => $currentLocale]) }}" class="mt-2">
                    @csrf
                    <button type="submit" class="min-h-11 w-full rounded-full border border-line-strong">
                        {{ __('header-logout') }}
                    </button>
                </form>
            @endauth

            @guest
                <a href="{{ route('login', ['locale' => $currentLocale]) }}"
                   class="mt-2 flex min-h-11 items-center justify-center rounded-full border border-line-strong">
                    {{ __('header-login') }}
                </a>
            @endguest
        </nav>
    </div>
</header>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('navToggle');
            const menu = document.getElementById('navMenu');

            const setOpen = (isOpen) => {
                menu.classList.toggle('hidden', !isOpen);
                toggle.setAttribute('aria-expanded', String(isOpen));
            };

            toggle?.addEventListener('click', () => setOpen(menu.classList.contains('hidden')));

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !menu.classList.contains('hidden')) {
                    setOpen(false);
                    toggle.focus();
                }
            });

            window.addEventListener('pageshow', () => setOpen(false));
        });
    </script>
@endpush
