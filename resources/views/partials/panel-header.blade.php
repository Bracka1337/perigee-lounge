@props(['links', 'badge', 'home'])

<header class="fixed top-0 left-0 z-50 w-full border-b border-line bg-ground/90 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-3 sm:px-6">
        <a href="{{ route($home) }}" class="flex min-h-11 min-w-0 items-center gap-3">
            <img src="{{ asset('images/logo.svg') }}" alt="" class="h-8 w-8 shrink-0">
            <span class="font-display text-lg font-medium tracking-wide">perigee</span>
            <span class="truncate rounded-full border border-nebula px-3 py-0.5 text-xs text-nebula">{{ $badge }}</span>
        </a>

        <nav class="hidden items-center gap-6 text-sm lg:flex">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}"
                   @if (request()->routeIs($link['active'])) aria-current="page" @endif
                   class="flex min-h-11 items-center transition hover:text-ink {{ request()->routeIs($link['active']) ? 'text-ink' : 'text-ink-muted' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach

            <a href="{{ route('home') }}" class="flex min-h-11 items-center border-l border-line pl-6 text-ink-muted transition hover:text-ink">
                {{ __('header-home') }}
            </a>
            <span class="max-w-40 truncate text-ink-muted">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="min-h-11 rounded-full border border-line-strong px-5 transition hover:border-ink">
                    {{ __('header-logout') }}
                </button>
            </form>
        </nav>

        <button id="navToggle" type="button" aria-controls="navMenu" aria-expanded="false" aria-label="{{ __('header-menu') }}"
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-line-strong lg:hidden">
            <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
                <path d="M3 6h14M3 14h14"/>
            </svg>
        </button>
    </div>

    <div id="navMenu" class="hidden max-h-[calc(100svh-68px)] overflow-y-auto border-t border-line bg-ground lg:hidden">
        <nav class="flex flex-col gap-1 px-4 py-4 sm:px-6">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}"
                   @if (request()->routeIs($link['active'])) aria-current="page" @endif
                   class="flex min-h-11 items-center {{ request()->routeIs($link['active']) ? 'text-ink' : 'text-ink-soft' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach

            <a href="{{ route('home') }}" class="flex min-h-11 items-center text-ink-soft">{{ __('header-home') }}</a>
            <p class="py-2 text-sm text-ink-muted">{{ auth()->user()->name }}</p>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="min-h-11 w-full rounded-full border border-line-strong">
                    {{ __('header-logout') }}
                </button>
            </form>
        </nav>
    </div>
</header>
