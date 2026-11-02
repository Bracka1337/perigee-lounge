@extends('layouts.app')

@section('content')
    <div class="w-full bg-black text-white">
        <section class="relative min-h-screen flex items-center justify-center overflow-hidden">
            <div class="absolute inset-0 hero-bg"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/80 to-black"></div>

            <div class="relative z-10 px-6 mt-24 md:mt-32 max-w-3xl mx-auto text-center space-y-6">
                <p class="text-xs tracking-[0.35em] uppercase text-white/60">
                    {{ __('perigee-rest-name') }}
                </p>

                <h1 class="logo-font text-5xl md:text-6xl drop-shadow-[0_0_10px_rgba(255,255,255,0.7)]">
                    403
                </h1>

                <p class="text-sm md:text-base text-white/70">
                    {{ __('403-title') }}
                </p>

                @php
                    $locale = $currentLocale
                        ?? request()->route('locale')
                        ?? config('site.default_locale', 'ru');
                @endphp

                @auth
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-6">
                        <a href="{{ route('home', ['locale' => $locale]) }}"
                           class="px-8 py-3 rounded-full border border-white/80 text-xs md:text-sm uppercase tracking-[0.25em]
                              drop-shadow-[0_0_10px_rgba(255,255,255,0.7)] hover:bg-white/10 transition">
                            {{ __('to-main-button') }}
                        </a>

                        <a href="{{ route('dashboard', ['locale' => $locale]) }}"
                           class="px-8 py-3 rounded-full border border-white/30 text-xs md:text-sm uppercase tracking-[0.25em]
                              text-white/80 hover:text-white hover:border-white/70 hover:bg-white/5 transition">
                            {{ __('to-profile-button') }}
                        </a>
                    </div>
                @endauth

                @guest
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-6">
                        <a href="{{ route('home', ['locale' => $locale]) }}"
                           class="px-8 py-3 rounded-full border border-white/80 text-xs md:text-sm uppercase tracking-[0.25em]
                              drop-shadow-[0_0_10px_rgba(255,255,255,0.7)] hover:bg-white/10 transition">
                            {{ __('to-main-button') }}
                        </a>

                        <a href="{{ route('login', ['locale' => $locale]) }}"
                           class="px-8 py-3 rounded-full border border-white/30 text-xs md:text-sm uppercase tracking-[0.25em]
                              text-white/80 hover:text-white hover:border-white/70 hover:bg-white/5 transition">
                            {{ __('log-in-button') }}
                        </a>
                    </div>
                @endguest
            </div>
        </section>
    </div>
@endsection
