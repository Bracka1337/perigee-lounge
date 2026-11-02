@extends('layouts.app')

@section('seo_key', 'contacts')

@php
    $mapUrl = __('contacts-map-link');
    $socialLinks = collect([
        ['title' => __('contacts-instagram-title'), 'url' => __('contacts-instagram-link'), 'icon' => 'Instagram'],
        ['title' => __('contacts-facebook-title'), 'url' => __('contacts-facebook-link'), 'icon' => 'Facebook'],
    ])->filter(fn (array $link) => str_starts_with($link['url'], 'https://'));
@endphp

@section('content')
    <div class="mx-auto min-h-screen max-w-7xl px-6 pb-20 pt-28">
        <x-page-header :eyebrow="__('perigee-rest-name')" :title="__('header-contacts')" :lead="__('contacts-title')" />

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="space-y-7 rounded-2xl border border-line bg-surface p-7">
                <div>
                    <h2 class="mb-2 text-sm font-bold text-ink-muted">{{ __('contacts-adress-title') }}</h2>
                    <p class="flex items-center gap-3 text-lg">
                        <img src="{{ asset('icons/coolicons/Navigation/Map_Pin.svg') }}" alt="" class="h-5 w-5 invert">
                        {{ __('contacts-adress-content') }}
                    </p>
                </div>

                <div>
                    <h2 class="mb-2 text-sm font-bold text-ink-muted">{{ __('contacts-phone-title') }}</h2>
                    <a href="tel:{{ __('contacts-phone-content') }}" class="flex min-h-11 items-center gap-3 text-lg transition hover:text-nebula">
                        <img src="{{ asset('icons/coolicons/Communication/Phone.svg') }}" alt="" class="h-5 w-5 invert">
                        {{ __('contacts-phone-content') }}
                    </a>
                </div>

                <div>
                    <h2 class="mb-2 text-sm font-bold text-ink-muted">{{ __('contacts-email-title') }}</h2>
                    <a href="mailto:{{ __('contacts-email-content') }}" class="flex min-h-11 items-center gap-3 text-lg transition hover:text-nebula">
                        <img src="{{ asset('icons/coolicons/Communication/Mail.svg') }}" alt="" class="h-5 w-5 invert">
                        {{ __('contacts-email-content') }}
                    </a>
                </div>

                @if ($socialLinks->isNotEmpty())
                    <div>
                        <h2 class="mb-2 text-sm font-bold text-ink-muted">{{ __('contacts-social-title') }}</h2>
                        <div class="flex flex-wrap gap-5">
                            @foreach ($socialLinks as $link)
                                <a href="{{ $link['url'] }}" target="_blank" rel="noopener" class="flex min-h-11 items-center gap-3 transition hover:text-nebula">
                                    <img src="{{ asset("icons/coolicons/Social/{$link['icon']}.svg") }}" alt="" class="h-5 w-5 invert">
                                    {{ $link['title'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <h2 class="mb-2 text-sm font-bold text-ink-muted">{{ __('contacts-working-time-title') }}</h2>
                    <p class="leading-relaxed">
                        {{ __('contacts-working-time-content-line-1') }}<br>
                        {{ __('contacts-working-time-content-line-2') }}<br>
                        {{ __('contacts-working-time-content-line-3') }}
                    </p>
                    <p class="mt-2 text-sm text-ink-muted">{{ __('contacts-working-time-content') }}</p>
                </div>
            </div>

            <div class="relative min-h-96 overflow-hidden rounded-2xl border border-line bg-surface">
                @if (str_starts_with($mapUrl, 'https://'))
                    <iframe src="{{ $mapUrl }}" title="{{ __('contacts-adress-title') }}" class="absolute inset-0 h-full w-full border-0"
                            allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                @else
                    <svg aria-hidden="true" viewBox="0 0 900 620" preserveAspectRatio="xMidYMid slice" class="absolute inset-0 h-full w-full">
                        <ellipse cx="450" cy="310" rx="400" ry="210" fill="none" stroke="#3A3D57" transform="rotate(-18 450 310)"/>
                        <ellipse cx="450" cy="310" rx="290" ry="140" fill="none" stroke="#1E2033" transform="rotate(-18 450 310)"/>
                        <circle cx="450" cy="310" r="74" fill="#07080F" stroke="#3A3D57"/>
                        <circle cx="94" cy="424" r="7" fill="#B9A5FF"/>
                    </svg>
                @endif
            </div>
        </div>
    </div>
@endsection
