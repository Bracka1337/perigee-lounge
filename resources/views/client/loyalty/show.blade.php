@extends('layouts.client')

@section('client-header')
    <div class="mb-10">
        <p class="text-xs text-ink-muted mb-3">
            {{ __('client-loyalty-badge') }}
        </p>
        <h1 class="logo-font text-3xl md:text-4xl mb-2">
            {{ __('client-loyalty-title') }}
        </h1>
        <p class="text-sm md:text-base text-ink-muted">
            {{ __('client-loyalty-subtitle') }}
        </p>
    </div>
@endsection

@section('client-content')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="min-w-0 rounded-2xl border border-line bg-surface p-5  flex flex-col items-center justify-center overflow-hidden">
            <p class="text-xs text-ink-muted mb-3 text-center inline-flex items-center gap-2">
                <x-icon category="System" name="Qr_Code" class="w-5 h-5"/>
                {{ __('client-loyalty-qr-label') }}
            </p>

            <div class="w-full flex justify-center py-6">
                <div class="relative rounded-2xl p-6 bg-surface backdrop-blur-xl border border-line">

                    <div id="perigee-qr-wrap" class="relative w-[220px] h-[220px] rounded-2xl overflow-hidden">
                        <div id="perigee-qr"
                             class="w-[220px] h-[220px]"
                             data-qr='@json($qrData)'
                             data-logo="{{ asset('images/logo.png') }}"></div>
                    </div>
                </div>
            </div>

            <p class="mt-3 text-xs text-ink-muted text-center max-w-sm">
                {{ __('client-loyalty-qr-hint') }}
            </p>
        </div>

        <div class="min-w-0 rounded-2xl border border-line bg-surface p-5  flex flex-col justify-between overflow-hidden">
            <div>
                <h2 class="text-xs text-ink-muted mb-2 inline-flex items-center gap-2">
                    <x-icon category="User" name="User_01" class="w-5 h-5"/>
                    {{ __('client-loyalty-card-title') }}
                </h2>

                <p class="text-sm text-ink-soft mb-4">
                    {{ __('client-loyalty-card-text') }}
                </p>

                <div class="rounded-2xl bg-ground/50 border border-line px-4 py-3 text-xs text-ink overflow-hidden">
                    <div class="flex items-center justify-between gap-3 mb-2 min-w-0">
                        <span class="text-xs text-ink-muted inline-flex items-center gap-2">
                            <x-icon category="User" name="User_01" class="w-4 h-4"/>
                            {{ __('client-loyalty-card-holder') }}
                        </span>
                        <span class="text-sm truncate max-w-[60%]">
                            {{ $user->name }}
                        </span>
                    </div>

<div class="mb-2 min-w-0">
    <div class="flex items-start justify-between gap-3 min-w-0">
        <span class="text-xs text-ink-muted inline-flex items-center gap-2 shrink-0">
            <x-icon category="System" name="Qr_Code" class="w-4 h-4"/>
            {{ __('client-loyalty-card-code') }}
        </span>

        <div class="relative max-w-[60%] min-w-0 group">
            <div class="absolute inset-y-0 left-0 w-6 pointer-events-none bg-gradient-to-r from-ground to-transparent"></div>
            <div class="absolute inset-y-0 right-0 w-6 pointer-events-none bg-gradient-to-l from-ground to-transparent"></div>

            <div class="loyalty-code-scroll min-w-0 overflow-x-auto whitespace-nowrap text-right font-mono tracking-tight cursor-grab hide-scrollbar"
                 style="font-size: clamp(8px, 2.6vw, 14px);">
                {{ $user->loyalty_code }}
            </div>
        </div>
    </div>
</div>


                    <div class="flex items-center justify-between gap-3 min-w-0">
                        <span class="text-xs text-ink-muted inline-flex items-center gap-2">
                            <x-icon category="Finance" name="Euro" class="w-4 h-4"/>
                            {{ __('client-loyalty-card-balance') }}
                        </span>
                        <span class="text-base whitespace-nowrap">
                            {{ number_format($user->loyalty_balance, 2, '.', ' ') }} €
                        </span>
                    </div>
                </div>

                @if($earnings->isNotEmpty())
                    <div class="mt-4">
                        <h3 class="text-xs text-ink-muted mb-2 inline-flex items-center gap-2">
                            <x-icon category="Interface" name="Book" class="w-5 h-5"/>
                            {{ __('client-loyalty-history-title') }}
                        </h3>

                        <div class="max-h-40 overflow-y-auto overflow-x-hidden pr-1 space-y-1 text-xs text-ink-soft">
                            @foreach($earnings as $earning)
                                <div class="flex items-start justify-between gap-3 min-w-0">
                                    <div class="text-ink-muted min-w-0 break-words">
                                        {{ $earning->created_at->format('d.m.Y H:i') }}
                                        @if($earning->order)
                                            · #{{ $earning->order->id }}
                                            @if($earning->order->table)
                                                · {{ $earning->order->table->name }}
                                            @endif
                                        @endif
                                    </div>

                                    <div class="{{ $earning->amount >= 0 ? 'text-emerald-300' : 'text-rose-300' }} whitespace-nowrap shrink-0">
                                        @if($earning->amount >= 0)
                                            +{{ number_format($earning->amount, 2, '.', ' ') }} €
                                        @else
                                            {{ number_format($earning->amount, 2, '.', ' ') }} €
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="mt-4 text-xs text-ink-muted">
                        {{ __('client-loyalty-history-empty') }}
                    </p>
                @endif
            </div>

            <p class="mt-4 text-xs text-ink-muted">
                {{ __('client-loyalty-earn-rule') }}
            </p>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/qr-code-styling.js') }}" defer></script>
    <script src="{{ asset('js/perigee-qr.js') }}" defer></script>
    <style>
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .cursor-grabbing { cursor: grabbing; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const els = document.querySelectorAll('.loyalty-code-scroll');
            els.forEach(el => {
                let isDown = false;
                let startX = 0;
                let startScrollLeft = 0;

                // Drag-to-scroll (mouse)
                el.addEventListener('mousedown', (e) => {
                    isDown = true;
                    startX = e.pageX;
                    startScrollLeft = el.scrollLeft;
                    el.classList.add('cursor-grabbing');
                });

                window.addEventListener('mouseup', () => {
                    if (!isDown) return;
                    isDown = false;
                    el.classList.remove('cursor-grabbing');
                });

                el.addEventListener('mouseleave', () => {
                    if (!isDown) return;
                    isDown = false;
                    el.classList.remove('cursor-grabbing');
                });

                el.addEventListener('mousemove', (e) => {
                    if (!isDown) return;
                    const dx = e.pageX - startX;
                    el.scrollLeft = startScrollLeft - dx;
                });

                // Map vertical wheel to horizontal scroll for convenience
                el.addEventListener('wheel', (e) => {
                    const dominant = Math.abs(e.deltaY) >= Math.abs(e.deltaX);
                    if (dominant) {
                        el.scrollLeft += e.deltaY;
                        e.preventDefault();
                    }
                }, { passive: false });
            });
        });
    </script>
@endpush
