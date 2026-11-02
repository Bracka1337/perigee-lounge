@extends('layouts.app')

@section('seo_key', 'about')


@section('content')
    <div class="w-full min-h-screen bg-gradient-to-b from-black via-[#050816] to-black text-white pt-24 pb-16 overflow-x-hidden">

        <div class="max-w-6xl mx-auto px-6">

            <div class="relative mb-12 text-center">
                <div class="absolute inset-x-0 -top-10 h-32 bg-gradient-to-b from-white/15 via-white/5 to-transparent blur-3xl opacity-50"></div>

                <div class="relative inline-flex flex-col items-center gap-1 mb-4">
                    <p class="text-[10px] tracking-[0.35em] uppercase text-white/60">
                        {{__('perigee-rest-name')}}
                    </p>
                    <h1 class="logo-font text-4xl md:text-5xl drop-shadow-[0_0_14px_rgba(255,255,255,0.9)]">
                        {{__('perigee-name')}}
                    </h1>
                </div>

                <p class="text-xs uppercase tracking-[0.25em] text-white/50 mb-2">
                    {{ __('header-about') }}
                </p>

                <p class="text-sm md:text-base text-white/70 max-w-2xl mx-auto">
                    {{ __('about-title') }}
                </p>
            </div>

            <div class="grid gap-8 md:gap-10">

                <section class="rounded-3xl border border-white/10 bg-gradient-to-b from-white/5 via-black/80 to-black p-6 md:p-7 shadow-[0_18px_45px_rgba(0,0,0,0.9)]">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('about-section-title-1') }}
                    </h2>
                    <p class="text-sm md:text-base text-white/70 mb-3">
                        {{ __('about-section-content-1') }}
                    </p>
                    <p class="text-sm md:text-base text-white/60">
                        {{ __('about-section-content-2') }}
                    </p>
                </section>

                <section class="rounded-3xl border border-white/10 bg-gradient-to-b from-white/5 via-black/80 to-black p-6 md:p-7 shadow-[0_18px_45px_rgba(0,0,0,0.9)]">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('about-section-title-2') }}
                    </h2>
                    <p class="text-sm md:text-base text-white/70 mb-3">
                        {{ __('about-section-content-3') }}
                    </p>
                    <ul class="mt-2 space-y-2 text-sm md:text-base text-white/65 list-disc list-inside">
                        <li>{{ __('about-section-content-4') }}</li>
                        <li>{{ __('about-section-content-5') }}</li>
                        <li>{{ __('about-section-content-6') }}</li>
                    </ul>
                </section>

                <section class="rounded-3xl border border-white/10 bg-gradient-to-b from-white/5 via-black/80 to-black p-6 md:p-7 shadow-[0_18px_45px_rgba(0,0,0,0.9)]">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('about-section-title-3') }}
                    </h2>
                    <p class="text-sm md:text-base text-white/70 mb-3">
                        {{ __('about-section-content-7') }}
                    </p>
                    <p class="text-sm md:text-base text-white/60">
                        {{ __('about-section-content-8') }}
                    </p>
                </section>

                <section class="rounded-3xl border border-white/10 bg-gradient-to-b from-white/5 via-black/80 to-black p-6 md:p-7 shadow-[0_18px_45px_rgba(0,0,0,0.9)]">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('about-section-title-4') }}
                    </h2>
                    <p class="text-sm md:text-base text-white/70">
                        {{ __('about-section-content-9') }}
                    </p>
                    <ul class="mt-2 space-y-2 text-sm md:text-base text-white/65 list-disc list-inside">
                        <li>{{ __('about-section-content-10') }}</li>
                        <li>{{ __('about-section-content-11') }}</li>
                        <li>{{ __('about-section-content-12') }}</li>
                    </ul>
                    <p class="text-sm md:text-base text-white/60 mt-3">
                        {{ __('about-section-content-13') }}
                    </p>
                </section>

                <section class="rounded-3xl border border-white/10 bg-gradient-to-b from-white/5 via-black/80 to-black p-6 md:p-7 shadow-[0_18px_45px_rgba(0,0,0,0.9)]">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('about-section-title-5') }}
                    </h2>
                    <p class="text-sm md:text-base text-white/70">
                        {{ __('about-section-content-14') }}
                    </p>
                    <ul class="mt-2 space-y-2 text-sm md:text-base text-white/65 list-disc list-inside">
                        <li>{{ __('about-section-content-15') }}</li>
                        <li>{{ __('about-section-content-16') }}</li>
                        <li>{{ __('about-section-content-17') }}</li>
                    </ul>
                    <p class="text-sm md:text-base text-white/60 mt-3">
                        {{ __('about-section-content-18') }}
                    </p>
                </section>

            </div>

            <section class="mt-10 text-center space-y-4">
                <p class="text-sm md:text-base text-white/75">
                    {{ __('about-section-end') }}
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="{{ route('client.reservations.index', ['locale' => app()->getLocale()]) }}"
                       class="px-10 py-3 rounded-full border border-white/80 text-[11px] tracking-[0.25em] uppercase
                              text-white shadow-[0_0_14px_rgba(255,255,255,0.7)]
                              hover:bg-white hover:text-black transition">
                        {{ __('perigee-booking-button') }}
                    </a>
                    <a href="{{ route('menu') }}"
                       class="px-10 py-3 rounded-full border border-white/30 text-[11px] tracking-[0.25em] uppercase
                              text-white/80 hover:text-white hover:border-white/70 hover:bg-white/5 transition">
                        {{ __('perigee-menu-button') }}
                    </a>
                </div>
            </section>

        </div>

    </div>
@endsection
