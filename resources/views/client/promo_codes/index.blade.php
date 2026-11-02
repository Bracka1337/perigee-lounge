@extends('layouts.client')

@section('client-header')
    <div class="mb-10">
        <p class="text-xs text-ink-muted mb-3">
            {{ __('client-promo-codes-badge') }}
        </p>
        <h1 class="logo-font text-3xl md:text-4xl mb-2">
            {{ __('client-promo-codes-title') }}
        </h1>
        <p class="text-sm md:text-base text-ink-muted">
            {{ __('client-promo-codes-subtitle') }}
        </p>
    </div>
@endsection

@section('client-content')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="min-w-0 rounded-2xl border border-line bg-surface p-5  flex flex-col justify-between overflow-hidden">
            <div>
                <h2 class="text-xs text-ink-muted mb-2 inline-flex items-center gap-2">
                    <x-icon category="Edit" name="Add_Plus_Circle" class="w-5 h-5"/>
                    {{ __('client-promo-codes-add-title') }}
                </h2>

                <p class="text-sm text-ink-soft mb-4">
                    {{ __('client-promo-codes-add-text') }}
                </p>

                <form action="{{ route('client.promo-codes.store', ['locale' => app()->getLocale()]) }}"
                      method="POST"
                      class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-xs text-ink-muted mb-1">
                            {{ __('client-promo-codes-input-label') }}
                        </label>

                        <div class="flex items-center gap-2 min-w-0">
                            <input
                                type="text"
                                name="public_code"
                                class="min-w-0 w-full rounded-2xl bg-ground/40 border border-line px-3 py-2 text-sm text-ink placeholder:text-ink-muted focus:outline-none focus:ring-2 focus:ring-line-strong"
                                placeholder="{{ __('client-promo-codes-input-placeholder') }}"
                                required
                            >

                            <button type="submit"
                                    class="shrink-0 inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-2 text-xs bg-ink text-on-nebula hover:bg-line transition">
                                <x-icon category="System" name="Checkbox_Check" class="w-5 h-5"/>
                                {{ __('client-promo-codes-apply-button') }}
                            </button>
                        </div>

                        @error('public_code')
                            <p class="mt-1 text-xs text-red-300">{{ $message }}</p>
                        @enderror

                        @if(session('promo_success'))
                            <p class="mt-2 text-xs text-emerald-300">{{ session('promo_success') }}</p>
                        @endif

                        @if(session('promo_error'))
                            <p class="mt-2 text-xs text-red-300">{{ session('promo_error') }}</p>
                        @endif
                    </div>
                </form>
            </div>

            <p class="mt-4 text-xs text-ink-muted">
                {{ __('client-promo-codes-hint') }}
            </p>
        </div>

        <div class="min-w-0 rounded-2xl border border-line bg-surface p-5  overflow-hidden">
            <h2 class="text-xs text-ink-muted mb-2 inline-flex items-center gap-2">
                <x-icon category="Interface" name="Ticket_Voucher" class="w-5 h-5"/>
                {{ __('client-promo-codes-list-title') }}
            </h2>

            <p class="text-sm text-ink-soft mb-4">
                {{ __('client-promo-codes-list-text') }}
            </p>

            @if($promoCodes->isEmpty())
                <p class="text-sm text-ink-muted">
                    {{ __('client-promo-codes-empty') }}
                </p>
            @else
                <div class="space-y-2 max-h-72 overflow-y-auto overflow-x-hidden pr-1 text-xs text-ink-soft">
                    @foreach($promoCodes as $promo)
                        @php
                            $qrValue = $promo->qr_code;
                            $isUsed = ($promo->pivot->is_redeemed ?? false);
                            $isExpired = isset($promo->valid_until) && now()->gt($promo->valid_until);
                            $qrId = 'promo-qr-' . $promo->id;

                            $codeText = $promo->public_code
                                ?? (isset($promo->qr_code) ? substr($promo->qr_code, 0, 16) : ('#'.$promo->id));
                        @endphp

<div class="rounded-2xl bg-ground/40 border border-line p-4 overflow-hidden">
    <div class="flex flex-row gap-4 items-start min-w-0">
        <button type="button"
                onclick="openQrModal('{{ $qrValue }}')"
                class="shrink-0">
            <div class="relative rounded-2xl p-3 bg-surface backdrop-blur-xl border border-line">

                <div class="relative w-[84px] h-[84px] rounded-xl overflow-hidden">
                    <div class="promo-perigee-qr w-[84px] h-[84px]"
                         data-qr="{{ $qrValue }}"
                         data-logo="{{ asset('images/logo.png') }}"
                         id="{{ $qrId }}"></div>
                </div>
            </div>
        </button>

<div class="min-w-0 flex-1 flex flex-col items-end text-right">
    {{-- КОД --}}
    <div class="font-mono text-ink leading-tight break-all w-full"
         style="font-size: clamp(10px, 3.2vw, 14px);">
        {{ $codeText }}
    </div>

    {{-- ОПИСАНИЕ --}}
    @if(!empty($promo->description))
        <div class="mt-1 text-xs text-ink-muted break-words w-full">
            {{ $promo->description }}
        </div>
    @endif

    {{-- СКИДКА / ИСТЕКАЕТ --}}
    <div class="mt-3 space-y-2 text-xs text-ink-muted w-full">
        <div class="flex items-center justify-end gap-1">
            <x-icon category="Interface" name="Ticket_Voucher" class="w-4 h-4"/>
            {{ __('client-promo-codes-discount-label') }}:
            @if($promo->discount_type === 'percent')
                <span class="text-ink-soft">{{ $promo->discount_value }}%</span>
            @else
                <span class="text-ink-soft">{{ $promo->discount_value }}€</span>
            @endif
        </div>

        <div class="flex items-center justify-end gap-1">
            <x-icon category="Interface" name="Target" class="w-4 h-4"/>
            <span>{{ __('staff-promo-field-scope') }}:</span>
            @if($promo->scope === 'all')
                <span class="text-ink-soft">{{ __('staff-promo-scope-all') }}</span>
            @elseif($promo->scope === 'category')
                <span class="text-ink-soft">{{ __('staff-promo-scope-category') }}</span>
                @if($promo->menuCategory)
                    <span class="text-ink-soft">— {{ $promo->menuCategory->t('name') }}</span>
                @endif
            @elseif($promo->scope === 'item')
                <span class="text-ink-soft">{{ __('staff-promo-scope-item') }}</span>
                @if($promo->menuItem)
                    <span class="text-ink-soft">— {{ $promo->menuItem->t('name') }}</span>
                @endif
            @else
                <span class="text-ink-soft">—</span>
            @endif
        </div>

        @if(!empty($promo->valid_until))
            <div class="flex items-center justify-end gap-1">
                <x-icon category="Calendar" name="Calendar_Days" class="w-4 h-4"/>
                {{ __('client-promo-codes-expires-label') }}:
                <span class="text-ink-soft">{{ \Illuminate\Support\Carbon::parse($promo->valid_until)->format('d.m.Y') }}</span>
            </div>
        @endif
    </div>

    {{-- СТАТУС --}}
    <div class="mt-3 w-full flex justify-end">
        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs
            {{ $isUsed || $isExpired
                ? 'border border-line text-ink-muted bg-surface'
                : 'border border-emerald-400/40 text-emerald-200 bg-emerald-500/10' }}">
            @if($isUsed)
                <x-icon category="Interface" name="Checkbox_Check" class="w-4 h-4"/>
                {{ __('promo-used') }}
            @elseif($isExpired)
                <x-icon category="Warning" name="Triangle_Warning" class="w-4 h-4"/>
                {{ __('promo-expired') }}
            @else
                <x-icon category="Interface" name="Checkbox_Check" class="w-4 h-4"/>
                {{ __('promo-active') }}
            @endif
        </span>
    </div>
</div>

    </div>
</div>



                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div id="qrModal"
         class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-ground/90 backdrop-blur-md"
         style="
            padding-left: calc(1.25rem + env(safe-area-inset-left));
            padding-right: calc(1.25rem + env(safe-area-inset-right));
            padding-top: calc(2rem + env(safe-area-inset-top));
            padding-bottom: calc(2.5rem + env(safe-area-inset-bottom));
         "
         onclick="closeQrModal()">

        <div class="relative max-w-full max-h-full rounded-2xl bg-surface backdrop-blur-xl border border-line p-5 md:p-8"
             onclick="event.stopPropagation()">

            <div class="relative w-[min(72vw,360px)] aspect-square rounded-2xl overflow-hidden">
                <div id="promo-modal-qr" class="w-full h-full" data-logo="{{ asset('images/logo.png') }}"></div>
            </div>

            <button type="button"
                    onclick="closeQrModal()"
                    class="absolute -top-3 -right-3 w-9 h-9 rounded-full bg-ink text-on-nebula text-sm font-bold hover:bg-line transition"
                    aria-label="Close">
                ✕
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/qr-code-styling.js') }}" defer></script>
    <script src="{{ asset('js/perigee-qr-promo.js') }}" defer></script>

    <script>
        let promoModalQr = null;
        let promoModalValue = null;
        let promoModalRO = null;
        let promoModalRAF = null;

        function getModalQrSize(el) {
            const w = el.clientWidth || 0;
            const h = el.clientHeight || 0;
            return Math.max(180, Math.floor(Math.min(w, h)));
        }

        function renderPromoModalQr(force = false) {
            const el = document.getElementById('promo-modal-qr');
            if (!el || !promoModalValue || typeof QRCodeStyling === 'undefined') return;
            if (!force && promoModalRAF) return;

            promoModalRAF = requestAnimationFrame(() => {
                promoModalRAF = null;

                const size = getModalQrSize(el);

                el.innerHTML = '';
                promoModalQr = new QRCodeStyling({
                    width: size,
                    height: size,
                    data: promoModalValue,
                    margin: Math.max(10, Math.floor(size * 0.05)),
                    qrOptions: { typeNumber: 0, errorCorrectionLevel: "M" },

                    dotsOptions: {
                        type: "rounded",
                        gradient: {
                            type: "linear",
                            rotation: 90,
                            colorStops: [
                                { offset: 0, color: "#0B0C16" },
                                { offset: 1, color: "#0B0C16" }
                            ]
                        }
                    },

                    backgroundOptions: { color: "#F2F1F7" },

                    cornersSquareOptions: {
                        type: "extra-rounded",
                        color: "#0B0C16"
                    },

                    cornersDotOptions: { type: "dot", color: "#0B0C16" },

                    image: el.dataset.logo,
                    imageOptions: {
                        crossOrigin: "anonymous",
                        imageSize: 0.60,
                        margin: 2,
                        hideBackgroundDots: true
                    }
                });

                promoModalQr.append(el);
            });
        }

        function attachPromoModalAutoResize() {
            const el = document.getElementById('promo-modal-qr');
            if (!el) return;

            if (!promoModalRO && 'ResizeObserver' in window) {
                promoModalRO = new ResizeObserver(() => renderPromoModalQr());
                promoModalRO.observe(el);
            }

            window.addEventListener('resize', renderPromoModalQr, { passive: true });
            window.addEventListener('orientationchange', () => setTimeout(() => renderPromoModalQr(true), 150), { passive: true });
        }

        function detachPromoModalAutoResize() {
            const el = document.getElementById('promo-modal-qr');

            if (promoModalRO && el) {
                promoModalRO.unobserve(el);
                promoModalRO.disconnect();
                promoModalRO = null;
            }

            window.removeEventListener('resize', renderPromoModalQr);
        }

        function openQrModal(value) {
            const modal = document.getElementById('qrModal');
            const el = document.getElementById('promo-modal-qr');

            promoModalValue = value;

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            el.innerHTML = '';

            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    attachPromoModalAutoResize();
                    renderPromoModalQr(true);
                });
            });
        }

        function closeQrModal() {
            const modal = document.getElementById('qrModal');
            const el = document.getElementById('promo-modal-qr');

            modal.classList.add('hidden');
            modal.classList.remove('flex');

            detachPromoModalAutoResize();

            promoModalValue = null;d
            promoModalQr = null;
            el.innerHTML = '';
        }
    </script>
@endpush
