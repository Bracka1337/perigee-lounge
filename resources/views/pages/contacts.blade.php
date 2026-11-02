@extends('layouts.app')

@section('seo_key', 'contacts')


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
                    {{ __('header-contacts') }}
                </p>
                <p class="text-sm md:text-base text-white/70 max-w-2xl mx-auto">
                    {{ __('contacts-title') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12 items-stretch">

                <div class="h-full">
                    <div class="h-full space-y-6 rounded-3xl border border-white/10
                                bg-gradient-to-b from-white/5 via-black/80 to-black
                                p-6 md:p-7 shadow-[0_18px_45px_rgba(0,0,0,0.9)]">

                        <div>
                            <h2 class="text-xs uppercase tracking-[0.25em] text-white/60 mb-2">
                                {{ __('contacts-adress-title') }}
                            </h2>
                            <a href="{{ __('contacts-adress-link') }}"
                               target="_blank" rel="noopener"
                               class="inline-flex items-center gap-2 text-sm md:text-base text-white/80 hover:text-white transition">
                                <img src="{{ asset('icons/coolicons/Navigation/Map_Pin.svg') }}" alt="Location" class="w-4 h-4 invert">
                                <span>{{ __('contacts-adress-content') }}</span>
                            </a>
                        </div>

                        <div>
                            <h2 class="text-xs uppercase tracking-[0.25em] text-white/60 mb-2">
                                {{ __('contacts-phone-title') }}
                            </h2>
                            <a href="tel:{{ __('contacts-phone-content') }}"
                               class="inline-flex items-center gap-2 text-sm md:text-base text-white/80 hover:text-white transition">
                                <img src="{{ asset('icons/coolicons/Communication/Phone.svg') }}" alt="Phone" class="w-4 h-4 invert">
                                <span>{{ __('contacts-phone-content') }}</span>
                            </a>
                        </div>

                        <div>
                            <h2 class="text-xs uppercase tracking-[0.25em] text-white/60 mb-2">
                                {{ __('contacts-email-title') }}
                            </h2>
                            <a href="mailto:{{ __('contacts-email-content') }}"
                               class="inline-flex items-center gap-2 text-sm md:text-base text-white/80 hover:text-white transition">
                                <img src="{{ asset('icons/coolicons/Communication/Mail.svg') }}" alt="Email" class="w-4 h-4 invert">
                                <span>{{ __('contacts-email-content') }}</span>
                            </a>
                        </div>

                        <div>
                            <h2 class="text-xs uppercase tracking-[0.25em] text-white/60 mb-2">
                                {{ __('contacts-social-title') }}
                            </h2>
                            <div class="flex flex-wrap gap-3 text-sm">
                                <a href="{{ __('contacts-instagram-link') }}"
                                   target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-2 text-white/80 hover:text-white transition">
                                    <img src="{{ asset('icons/coolicons/Social/Instagram.svg') }}" alt="{{ __('contacts-instagram-title') }}" class="w-4 h-4 invert">
                                    <span>{{ __('contacts-instagram-title') }}</span>
                                </a>
                                <a href="{{ __('contacts-facebook-link') }}"
                                   target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-2 text-white/80 hover:text-white transition">
                                    <img src="{{ asset('icons/coolicons/Social/Facebook.svg') }}" alt="{{ __('contacts-facebook-title') }}" class="w-4 h-4 invert">
                                    <span>{{ __('contacts-facebook-title') }}</span>
                                </a>
                            </div>
                        </div>

                        <div>
                            <h2 class="text-xs uppercase tracking-[0.25em] text-white/60 mb-2">
                                {{ __('contacts-working-time-title') }}
                            </h2>
                            <p class="text-sm text-white/80">
                                {{ __('contacts-working-time-content-line-1') }}<br>
                                {{ __('contacts-working-time-content-line-2') }}<br>
                                {{ __('contacts-working-time-content-line-3') }}
                            </p>
                            <p class="text-[11px] text-white/40 mt-1">
                                {{ __('contacts-working-time-content') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="h-full">
<div class="rounded-3xl border border-white/10
            bg-gradient-to-b from-white/5 via-black/80 to-black
            shadow-[0_18px_45px_rgba(0,0,0,0.9)]
            overflow-hidden relative min-h-[420px] md:min-h-0 md:h-full">
    <iframe
        src="{{ __('contacts-map-link') }}"
        class="absolute inset-0 w-full h-full"
        style="border:0;"
        allowfullscreen=""
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade">
    </iframe>
</div>

                </div>
            </div>

            {{--
            <div class="mt-8 rounded-3xl border border-white/10 bg-gradient-to-b from-white/5 via-black/80 to-black p-6 md:p-7 shadow-[0_18px_45px_rgba(0,0,0,0.9)]">
                <h2 class="text-center text-lg md:text-xl font-semibold mb-4 tracking-wide">
                    {{ __('contacts-form-title') }}
                </h2>
                <p class="text-center text-sm text-white/60 mb-6">
                    {{ __('contacts-form-content') }}
                </p>

                <form class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs uppercase tracking-[0.25em] text-white/60 mb-2">
                                {{ __('contacts-form-name') }}
                            </label>
                            <input type="text"
                                   class="w-full bg-black/40 border border-white/20 rounded-full px-4 py-2.5 text-sm
                                          focus:outline-none focus:border-white focus:ring-1 focus:ring-white/70"
                                   placeholder="{{ __('contacts-form-name-placeholder') }}">
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-[0.25em] text-white/60 mb-2">
                                {{ __('contacts-form-email') }}
                            </label>
                            <input type="email"
                                   class="w-full bg-black/40 border border-white/20 rounded-full px-4 py-2.5 text-sm
                                          focus:outline-none focus:border-white focus:ring-1 focus:ring-white/70"
                                   placeholder="{{ __('contacts-form-email-placeholder') }}">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-[0.25em] text-white/60 mb-2">
                            {{ __('contacts-form-message') }}
                        </label>
                        <textarea
                            rows="4"
                            class="w-full bg-black/40 border border-white/20 rounded-3xl px-4 py-3 text-sm
                                   focus:outline-none focus:border-white focus:ring-1 focus:ring-white/70 resize-none"
                            placeholder="{{ __('contacts-form-message-placeholder') }}"></textarea>
                    </div>

                    <div class="flex justify-center">
                        <button type="button"
                                class="px-10 py-3 rounded-full border border-white/80 text-[11px] tracking-[0.25em] uppercase
                                       text-white shadow-[0_0_14px_rgba(255,255,255,0.6)]
                                       hover:bg-white hover:text-black transition">
                            {{ __('contacts-form-send-button') }}
                        </button>
                    </div>

                   <p class="text-[11px] text-center text-white/40 mt-2">
                        Форма пока декоративная. Позже здесь можно подключить реальную отправку на email.
                    </p>
                </form>
            </div>
            --}}

        </div>

    </div>
@endsection
