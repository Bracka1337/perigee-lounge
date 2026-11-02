@extends('layouts.app')

@section('seo_key', 'home')

@section('content')

<div class="w-full bg-black text-white">

    <section class="relative min-h-screen overflow-hidden">
        <div class="absolute inset-0 hero-bg"></div>

        <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/80 to-black"></div>

        <div class="relative z-10 text-center px-6 pt-68 md:pt-88">

            <p class="text-xs tracking-[0.35em] uppercase text-white/60 mb-4">
                {{ __('perigee-rest-name') }}
            </p>
            <h1 class="logo-font text-4xl md:text-6xl mb-4 drop-shadow-[0_0_10px_rgba(255,255,255,0.7)]">
                {{ __('perigee-name') }}
            </h1>
            <p class="text-sm md:text-base text-white/80 mb-10">
                {{ __('home-page-title') }}
            </p>

<div class="flex flex-col sm:flex-row items-center justify-center gap-4">
    <a href="{{ route('client.reservations.index', ['locale' => app()->getLocale()]) }}"
       class="flex items-center justify-center w-64 h-12
              rounded-full border border-white/80 text-xs md:text-sm uppercase tracking-[0.25em]
              drop-shadow-[0_0_10px_rgba(255,255,255,0.7)] hover:bg-white/10 transition">
        {{ __('perigee-booking-button') }}
    </a>
    <a href="{{ route('menu', ['locale' => app()->getLocale()]) }}"
       class="flex items-center justify-center w-64 h-12
              rounded-full border border-white/30 text-xs md:text-sm uppercase tracking-[0.25em]
              text-white/80 hover:text-white hover:border-white/70 hover:bg-white/5 transition">
        {{ __('perigee-menu-button') }}
    </a>
</div>



        </div>

        <div class="pointer-events-none absolute inset-x-0 top-[55%]">
            <div id="star-field" class="relative w-full h-104 md:h-122 overflow-hidden"></div>
        </div>
    </section>


    <section class="py-16 px-6 border-t border-white/10 bg-black">
        <div class="max-w-4xl mx-auto text-center space-y-6">
            <h2 class="text-2xl md:text-3xl font-semibold drop-shadow-[0_0_8px_rgba(255,255,255,0.6)]">
                {{ __('home-page-section-title') }}
            </h2>
            <p class="text-white/70 text-sm md:text-base">
                {{ __('home-page-section-content') }}
            </p>
        </div>
    </section>

</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('star-field')
            if (!container) return

            const starCount = 80

            for (let i = 0; i < starCount; i++) {
                const star = document.createElement('span')
                star.className = 'star-point'

                const size = 1 + Math.random() * 2
                star.style.width = size + 'px'
                star.style.height = size + 'px'

                star.style.left = Math.random() * 100 + '%'
                star.style.top = Math.random() * 100 + '%'

                star.style.animationDelay = (Math.random() * 5).toFixed(2) + 's'
                star.style.animationDuration = (3 + Math.random() * 4).toFixed(2) + 's'

                container.appendChild(star)
            }
        })
    </script>
@endpush

@endsection
