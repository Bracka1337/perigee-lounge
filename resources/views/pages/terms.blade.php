@extends('layouts.app')

@section('content')
    <div class="w-full min-h-screen bg-ground text-ink pt-24 pb-16 overflow-x-hidden">

        <div class="mx-auto max-w-4xl px-6">
            <x-page-header :eyebrow="__('perigee-rest-name')" :title="__('page-terms-title')" :lead="__('page-updated-label').': '.now()->translatedFormat('d F Y')" />

            <div class="grid gap-8 md:gap-10">

                <section class="rounded-2xl border border-line bg-surface p-6 md:p-7">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('terms-section-2-title') }}
                    </h2>
                    <p class="text-sm md:text-base text-ink-muted">
                        {{ __('terms-section-2-text') }}
                    </p>
                </section>

                <section class="rounded-2xl border border-line bg-surface p-6 md:p-7">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('terms-section-3-title') }}
                    </h2>
                    <p class="text-sm md:text-base text-ink-muted">
                        {{ __('terms-section-3-text') }}
                    </p>
                </section>

                <section class="rounded-2xl border border-line bg-surface p-6 md:p-7">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('terms-section-4-title') }}
                    </h2>
                    <p class="text-sm md:text-base text-ink-muted">
                        {{ __('terms-section-4-text') }}
                    </p>
                </section>

                <section class="rounded-2xl border border-line bg-surface p-6 md:p-7">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('terms-section-5-title') }}
                    </h2>
                    <p class="text-sm md:text-base text-ink-muted">
                        {{ __('terms-section-5-text') }}
                    </p>
                </section>

                <section class="rounded-2xl border border-line bg-surface p-6 md:p-7">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('terms-section-6-title') }}
                    </h2>
                    <p class="text-sm md:text-base text-ink-muted">
                        {{ __('terms-section-6-text') }}
                    </p>
                </section>

                <section class="rounded-2xl border border-line bg-surface p-6 md:p-7">
                    <h2 class="text-lg md:text-xl font-semibold tracking-wide mb-3">
                        {{ __('terms-section-7-title') }}
                    </h2>
                    <p class="text-sm md:text-base text-ink-muted">
                        {{ __('terms-section-7-text') }}
                    </p>
                </section>

            </div>

        </div>

    </div>
@endsection
