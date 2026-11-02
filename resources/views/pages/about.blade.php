@extends('layouts.app')

@section('seo_key', 'about')

@section('content')
    <div class="mx-auto min-h-screen max-w-7xl px-6 pb-20 pt-28">
        <x-page-header :eyebrow="__('perigee-rest-name')" :title="__('header-about')" :lead="__('about-title')" />

        <div class="grid gap-4 md:grid-cols-2">
            @foreach ([1, 2, 3, 4] as $section)
                <section class="rounded-2xl border border-line bg-surface p-7">
                    <h2 class="mb-3 text-xl font-bold">{{ __("about-section-title-{$section}") }}</h2>
                    <p class="leading-relaxed text-ink-muted">{{ __("about-section-content-{$section}") }}</p>
                </section>
            @endforeach
        </div>

        <section class="mt-14 flex flex-wrap items-center justify-between gap-6 border-t border-line pt-10">
            <p class="font-display text-2xl font-light">{{ __('about-section-end') }}</p>
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
        </section>
    </div>
@endsection
