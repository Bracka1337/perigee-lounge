@php($locale = app()->getLocale())

<footer class="border-t border-line">
    <div class="mx-auto max-w-7xl px-6 py-10 text-sm text-ink-muted">
        <div class="flex flex-wrap items-center justify-between gap-x-12 gap-y-3">
            <span class="font-display text-ink">perigee</span>
            <a href="{{ __('contacts-adress-link') }}" class="flex min-h-11 items-center transition hover:text-ink">{{ __('contacts-adress-content') }}</a>
            <a href="tel:{{ __('contacts-phone-content') }}" class="flex min-h-11 items-center transition hover:text-ink">{{ __('contacts-phone-content') }}</a>
            <a href="mailto:{{ __('contacts-email-content') }}" class="flex min-h-11 items-center transition hover:text-ink">{{ __('contacts-email-content') }}</a>
        </div>

        <div class="mt-6 flex flex-wrap items-center justify-between gap-x-8 gap-y-2 border-t border-line pt-4 text-xs">
            <div class="flex flex-wrap gap-x-6">
                <a href="{{ route('terms', ['locale' => $locale]) }}" class="flex min-h-11 items-center transition hover:text-ink">{{ __('footer-terms-conditions') }}</a>
                <a href="{{ route('privacy', ['locale' => $locale]) }}" class="flex min-h-11 items-center transition hover:text-ink">{{ __('footer-privacy-policy') }}</a>
                <a href="{{ route('cookies', ['locale' => $locale]) }}" class="flex min-h-11 items-center transition hover:text-ink">{{ __('footer-cookie-policy') }}</a>
            </div>
            <p>© {{ now()->year }} {{ config('app.name') }}. {{ __('footer-rights') }}</p>
        </div>
    </div>
</footer>
