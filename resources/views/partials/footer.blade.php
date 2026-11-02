<footer class="w-full border-t border-white/10 bg-black/95">
    <div class="max-w-6xl mx-auto px-6 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
            <div class="flex justify-center md:justify-start gap-3">
            <img src="{{ asset('images/logo.png') }}" alt="PERIGEE logo"
                class="h-6 md:h-7 w-auto object-contain translate-y-[15px]">
                <div class="flex flex-col leading-tight">
                    <span class="text-xs tracking-[0.35em] uppercase text-white/60">{{ __('perigee-rest-name') }}</span>
                    <span class="logo-font text-2xl md:text-3xl text-white drop-shadow-[0_0_8px_rgba(255,255,255,0.6)]">
                        {{ __('perigee-name') }}
                    </span>
                </div>
            </div>

            <div class="flex flex-col items-center gap-3 text-xs text-white/60">
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="{{ __('contacts-instagram-link') }}"
                       class="flex items-center gap-2 hover:text-white transition"
                       target="_blank" rel="noopener">
                        <img src="{{ asset('icons/coolicons/Social/Instagram.svg') }}" alt="{{ __('contacts-instagram-title') }}" class="w-4 h-4 invert">
                        <span>{{ __('contacts-instagram-title') }}</span>
                    </a>
                    <a href="{{ __('contacts-facebook-link') }}"
                       class="flex items-center gap-2 hover:text-white transition"
                       target="_blank" rel="noopener">
                        <img src="{{ asset('icons/coolicons/Social/Facebook.svg') }}" alt="{{ __('contacts-facebook-title') }}" class="w-4 h-4 invert">
                        <span>{{ __('contacts-facebook-title') }}</span>
                    </a>
                </div>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="mailto:{{ __('contacts-email-content') }}"
                       class="flex items-center gap-2 hover:text-white transition">
                        <img src="{{ asset('icons/coolicons/Communication/Mail.svg') }}" alt="Email" class="w-4 h-4 invert">
                        <span>{{ __('contacts-email-content') }}</span>
                    </a>
                    <a href="tel:{{ __('contacts-phone-content') }}"
                       class="flex items-center gap-2 hover:text-white transition">
                        <img src="{{ asset('icons/coolicons/Communication/Phone.svg') }}" alt="Phone" class="w-4 h-4 invert">
                        <span>{{ __('contacts-phone-content') }}</span>
                    </a>
                </div>
            </div>

            <div class="flex flex-col items-center md:items-end gap-2 text-xs text-white/60">
                <a href="{{ __('contacts-adress-link') }}"
                   class="flex items-center gap-2 hover:text-white transition text-center md:text-right"
                   target="_blank" rel="noopener">
                    <img src="{{ asset('icons/coolicons/Navigation/Map_Pin.svg') }}" alt="Address" class="w-4 h-4 invert">
                    <span>{{ __('contacts-adress-content') }}</span>
                </a>
            </div>
        </div>

        <div class="border-t border-white/10 mt-6 pt-4">
            <div class="flex flex-col items-center gap-3 text-[10px]">
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('terms') }}" class="text-white/40 hover:text-white transition">
                        {{ __('footer-terms-conditions') }}
                    </a>
                    <a href="{{ route('privacy') }}" class="text-white/40 hover:text-white transition">
                        {{ __('footer-privacy-policy') }}
                    </a>
                    <a href="{{ route('cookies') }}" class="text-white/40 hover:text-white transition">
                        {{ __('footer-cookie-policy') }}
                    </a>
                </div>
                <p class="text-[10px] text-white/40 text-center">
                    © {{ now()->year }} {{ __('perigee-name') }} {{ __('perigee-rest-name') }}. {{__('footer-rights')}}
                </p>
            </div>
        </div>
    </div>
</footer>
