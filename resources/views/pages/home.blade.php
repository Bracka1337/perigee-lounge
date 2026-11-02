@extends('layouts.app')

@section('seo_key', 'home')

@section('content')
    <section class="relative overflow-hidden">
        <svg aria-hidden="true" viewBox="0 0 900 620"
             class="pointer-events-none absolute -right-44 top-10 h-auto w-[900px] max-w-[110%]">
            <ellipse cx="450" cy="310" rx="400" ry="210" fill="none" stroke="#3A3D57" transform="rotate(-18 450 310)"/>
            <ellipse cx="450" cy="310" rx="290" ry="140" fill="none" stroke="#1E2033" transform="rotate(-18 450 310)"/>
            <circle cx="450" cy="310" r="74" fill="#0E0F1B" stroke="#3A3D57"/>
            <circle cx="94" cy="424" r="7" fill="#B9A5FF"/>
            <g fill="#F2F1F7">
                <circle class="star" cx="120" cy="90" r="1.5"/>
                <circle class="star" cx="240" cy="44" r="1"/>
                <circle class="star" cx="330" cy="150" r="1.5"/>
                <circle class="star" cx="610" cy="60" r="2"/>
                <circle class="star" cx="760" cy="150" r="1"/>
                <circle class="star" cx="830" cy="350" r="1.5"/>
                <circle class="star" cx="640" cy="520" r="1"/>
                <circle class="star" cx="250" cy="560" r="1.5"/>
                <circle class="star" cx="500" cy="590" r="1"/>
                <circle class="star" cx="40" cy="240" r="1"/>
            </g>
        </svg>

        <div class="relative mx-auto max-w-7xl px-6 pb-32 pt-40 md:pt-48">
            <div class="max-w-3xl">
                <p class="mb-6 text-xs font-bold uppercase tracking-[0.22em] text-nebula">
                    {{ __('perigee-rest-name') }}
                </p>
                <h1 class="mb-7 font-display text-[clamp(2.5rem,6.4vw,5.25rem)] font-light leading-[1.08] tracking-tight">
                    {{ __('home-hero-title') }}
                </h1>
                <p class="mb-10 max-w-xl text-lg leading-relaxed text-ink-soft">
                    {{ __('home-page-title') }}
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('client.reservations.index', ['locale' => app()->getLocale()]) }}"
                       class="inline-flex min-h-13 items-center rounded-full bg-nebula px-8 font-bold text-on-nebula transition hover:bg-ink">
                        {{ __('perigee-booking-button') }}
                    </a>
                    <a href="{{ route('menu', ['locale' => app()->getLocale()]) }}"
                       class="inline-flex min-h-13 items-center rounded-full border border-line-strong px-8 font-medium transition hover:border-ink">
                        {{ __('perigee-menu-button') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="border-y border-line">
        <div class="mx-auto grid max-w-7xl gap-8 px-6 py-14 md:grid-cols-3">
            @foreach ([1, 2, 3] as $feature)
                <div>
                    <h2 class="mb-2.5 text-lg font-bold">{{ __("home-feature-title-{$feature}") }}</h2>
                    <p class="leading-relaxed text-ink-muted">{{ __("home-feature-content-{$feature}") }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endsection
