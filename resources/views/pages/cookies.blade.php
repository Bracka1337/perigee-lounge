@extends('layouts.app')

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
                    {{ __('page-cookies-title') }}
                </p>

                <p class="text-sm text-white/50">
                    {{ __('page-updated-label') }}: {{ now()->translatedFormat('d F Y') }}
                </p>
            </div>

            <div class="grid gap-8 md:gap-10">

                <section class="rounded-3xl border border-white/10 bg-gradient-to-b from-white/5 via-black/80 to-black p-6 md:p-7 shadow-[0_18px_45px_rgba(0,0,0,0.9)]">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('cookies-section-2-title') }}
                    </h2>
                    <p class="text-sm md:text-base text-white/70 mb-3">
                        {{ __('cookies-section-2-text') }}
                    </p>
                    <ul class="mt-2 space-y-2 text-sm md:text-base text-white/65 list-disc list-inside">
                        <li>{{ __('cookies-section-2-item-1') }}</li>
                        <li>{{ __('cookies-section-2-item-2') }}</li>
                        <li>{{ __('cookies-section-2-item-3') }}</li>
                    </ul>
                </section>

                <section class="rounded-3xl border border-white/10 bg-gradient-to-b from-white/5 via-black/80 to-black p-6 md:p-7 shadow-[0_18px_45px_rgba(0,0,0,0.9)]">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('cookies-section-3-title') }}
                    </h2>
                    <p class="text-sm md:text-base text-white/70 mb-3">
                        {{ __('cookies-section-3-text') }}
                    </p>
                    <ul class="mt-2 space-y-2 text-sm md:text-base text-white/65 list-disc list-inside">
                        <li>{{ __('cookies-section-3-item-1') }}</li>
                        <li>{{ __('cookies-section-3-item-2') }}</li>
                        <li>{{ __('cookies-section-3-item-3') }}</li>
                        <li>{{ __('cookies-section-3-item-4') }}</li>
                        <li>{{ __('cookies-section-3-item-5') }}</li>
                    </ul>
                </section>

                <section class="rounded-3xl border border-white/10 bg-gradient-to-b from-white/5 via-black/80 to-black p-6 md:p-7 shadow-[0_18px_45px_rgba(0,0,0,0.9)]">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('cookies-section-4-title') }}
                    </h2>
                    <p class="text-sm md:text-base text-white/70">
                        {{ __('cookies-section-4-text') }}
                    </p>
                </section>

                <section class="rounded-3xl border border-white/10 bg-gradient-to-b from-white/5 via-black/80 to-black p-6 md:p-7 shadow-[0_18px_45px_rgba(0,0,0,0.9)]">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('cookies-section-5-title') }}
                    </h2>
                    <p class="text-sm md:text-base text-white/70">
                        {{ __('cookies-section-5-text') }}
                    </p>
                </section>

                <section class="rounded-3xl border border-white/10 bg-gradient-to-b from-white/5 via-black/80 to-black p-6 md:p-7 shadow-[0_18px_45px_rgba(0,0,0,0.9)]">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('cookies-section-6-title') }}
                    </h2>
                    <p class="text-sm md:text-base text-white/70">
                        {{ __('cookies-section-6-text') }}
                    </p>
                </section>

                <section class="rounded-3xl border border-white/10 bg-gradient-to-b from-white/5 via-black/80 to-black p-6 md:p-7 shadow-[0_18px_45px_rgba(0,0,0,0.9)]">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('cookies-section-7-title') }}
                    </h2>
                    <p class="text-sm md:text-base text-white/70">
                        {{ __('cookies-section-7-text') }}
                    </p>
                </section>

            </div>

        </div>

    </div>
@endsection
