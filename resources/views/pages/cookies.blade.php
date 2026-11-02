@extends('layouts.app')

@section('content')
    <div class="w-full min-h-screen bg-ground text-ink pt-24 pb-16 overflow-x-hidden">

        <div class="mx-auto max-w-4xl px-6">
            <x-page-header :eyebrow="__('perigee-rest-name')" :title="__('page-cookies-title')" :lead="__('page-updated-label').': '.now()->translatedFormat('d F Y')" />

            <div class="grid gap-8 md:gap-10">

                <section class="rounded-2xl border border-line bg-surface p-6 md:p-7">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('cookies-section-2-title') }}
                    </h2>
                    <p class="text-sm md:text-base text-ink-muted mb-3">
                        {{ __('cookies-section-2-text') }}
                    </p>
                    <ul class="mt-2 space-y-2 text-sm md:text-base text-ink-muted list-disc list-inside">
                        <li>{{ __('cookies-section-2-item-1') }}</li>
                        <li>{{ __('cookies-section-2-item-2') }}</li>
                        <li>{{ __('cookies-section-2-item-3') }}</li>
                    </ul>
                </section>

                <section class="rounded-2xl border border-line bg-surface p-6 md:p-7">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('cookies-section-3-title') }}
                    </h2>
                    <p class="text-sm md:text-base text-ink-muted mb-3">
                        {{ __('cookies-section-3-text') }}
                    </p>
                    <ul class="mt-2 space-y-2 text-sm md:text-base text-ink-muted list-disc list-inside">
                        <li>{{ __('cookies-section-3-item-1') }}</li>
                        <li>{{ __('cookies-section-3-item-2') }}</li>
                        <li>{{ __('cookies-section-3-item-3') }}</li>
                        <li>{{ __('cookies-section-3-item-4') }}</li>
                        <li>{{ __('cookies-section-3-item-5') }}</li>
                    </ul>
                </section>

                <section class="rounded-2xl border border-line bg-surface p-6 md:p-7">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('cookies-section-4-title') }}
                    </h2>
                    <p class="text-sm md:text-base text-ink-muted">
                        {{ __('cookies-section-4-text') }}
                    </p>
                </section>

                <section class="rounded-2xl border border-line bg-surface p-6 md:p-7">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('cookies-section-5-title') }}
                    </h2>
                    <p class="text-sm md:text-base text-ink-muted">
                        {{ __('cookies-section-5-text') }}
                    </p>
                </section>

                <section class="rounded-2xl border border-line bg-surface p-6 md:p-7">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('cookies-section-6-title') }}
                    </h2>
                    <p class="text-sm md:text-base text-ink-muted">
                        {{ __('cookies-section-6-text') }}
                    </p>
                </section>

                <section class="rounded-2xl border border-line bg-surface p-6 md:p-7">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('cookies-section-7-title') }}
                    </h2>
                    <p class="text-sm md:text-base text-ink-muted">
                        {{ __('cookies-section-7-text') }}
                    </p>
                </section>

            </div>

        </div>

    </div>
@endsection
