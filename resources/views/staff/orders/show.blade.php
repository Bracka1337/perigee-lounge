@extends('layouts.staff')

@php
    $locale = app()->getLocale();
@endphp

@push('styles')
<style>
    .no-x-scroll { overflow-x: hidden; }
    #qr-video { width: 100% !important; height: 100% !important; object-fit: cover !important; border-radius: 1rem; display: block; }
</style>
@endpush

@section('content')
<div class="no-x-scroll">

    <div class="mb-6">
        <a href="{{ route('staff.orders.index', ['locale' => $locale]) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 text-sm text-white/80 transition">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="w-4 h-4">
                <path d="M19 12H5m5-7l-7 7 7 7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>{{ __('staff-back-to-menu') }}</span>
        </a>
    </div>

    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="min-w-0">
            <p class="text-[10px] uppercase tracking-[0.25em] text-white/60 mb-2">
                {{ __('staff-orders-show-badge') }}
            </p>
            <h1 class="logo-font text-3xl md:text-4xl mb-1 drop-shadow-[0_0_10px_rgba(255,255,255,0.7)]">
                {{ __('staff-orders-show-title', ['id' => $order->id]) }}
            </h1>
            <p class="text-sm text-white/70">
                {{ __('staff-orders-show-subtitle', ['table' => $order->table->name ?? '#']) }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-xs">
            <div class="px-3 py-1 rounded-full border border-white/30 text-white/80 inline-flex items-center gap-2">
                <x-icon category="Interface" name="Info_Circle" class="w-4 h-4"/>
                <span>{{ __('staff-orders-status-label') }}: {{ __('staff-orders-status-'.$order->status) }}</span>
            </div>

            <div class="px-3 py-1 rounded-full border border-white/30 text-white/80 inline-flex items-center gap-2">
                <x-icon category="Finance" name="Money" class="w-4 h-4"/>
                <span>{{ __('staff-orders-total-label') }}: {{ number_format($order->total, 2, '.', ' ') }} €</span>
            </div>

        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 text-xs text-emerald-300 border border-emerald-500/60 bg-emerald-500/10 px-4 py-2 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 text-xs text-red-300 border border-red-500/60 bg-red-500/10 px-4 py-2 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-2xl border border-white/10 bg-white/5 p-4 min-w-0">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-3">
                <h2 class="text-xs uppercase tracking-[0.22em] text-white/60 inline-flex items-center gap-2">
                    <x-icon category="Interface" name="List_Check" class="w-5 h-5"/>
                    {{ __('staff-orders-items-title') }}
                </h2>

                @if($order->status === \App\Models\Order::STATUS_OPEN)
                    <a href="{{ route('staff.orders.items.catalog', ['locale' => $locale, 'order' => $order]) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 border border-white/20 text-sm text-white transition">
                        <x-icon category="Shopping" name="Shopping_Cart_01" class="w-5 h-5"/>
                        <span>{{ __('staff-orders-select-items') }}</span>
                    </a>
                @endif
            </div>

            <div class="space-y-3">
                @forelse($order->items as $item)
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-3 py-2 rounded-xl bg-black/40 border border-white/10 min-w-0">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($item->menuItem->image_path ?? false)
                                <img src="{{ asset('storage/'.$item->menuItem->image_path) }}"
                                     alt="{{ $item->menuItem->name[$locale] ?? '' }}"
                                     class="w-10 h-10 rounded-lg object-cover shrink-0">
                            @endif
                            <div class="min-w-0">
                                <div class="text-xs font-medium truncate">
                                    {{ $item->menuItem->name[$locale] ?? '' }}
                                </div>
                                <div class="text-[11px] text-white/50">
                                    {{ number_format($item->unit_price, 2, '.', ' ') }} € × {{ $item->quantity }}
                                </div>
                            </div>
                        </div>

                        @if($order->status === \App\Models\Order::STATUS_OPEN)
                            <div class="flex flex-wrap items-center gap-2 justify-end">
                                <form method="POST"
                                      action="{{ route('staff.orders.items.served', ['locale' => $locale, 'order' => $order, 'item' => $item]) }}"
                                      class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <label class="flex items-center gap-2 cursor-pointer px-2.5 py-1.5 rounded-xl border border-amber-400/50 bg-amber-500/10 hover:bg-amber-400/20 transition shadow-[0_6px_18px_rgba(251,191,36,0.15)]">
                                        <input type="checkbox"
                                               {{ $item->is_served ? 'checked' : '' }}
                                               onchange="this.form.submit()"
                                               class="w-4 h-4 rounded-md border-amber-400/80 text-amber-500 bg-black/50 focus:ring-amber-300 focus:ring-1 cursor-pointer">
                                        <span class="text-[10px] text-amber-100 font-semibold uppercase tracking-[0.16em]">{{ __('staff-orders-item-served') }}</span>
                                    </label>
                                </form>

                                <form method="POST"
                                      action="{{ route('staff.orders.items.update', ['locale' => $locale, 'order' => $order, 'item' => $item]) }}"
                                      class="flex items-center gap-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number"
                                           name="quantity"
                                           value="{{ $item->quantity }}"
                                           min="1"
                                           max="99"
                                           class="w-16 bg-black/60 border border-white/20 rounded-lg px-2 py-1 text-xs text-white focus:outline-none focus:ring-1 focus:ring-white/70">
                                    <button type="submit"
                                            aria-label="{{ __('staff-orders-item-update-button') }}"
                                            class="px-3 py-1 rounded-full border border-white/40 text-white hover:bg-white hover:text-black transition">
                                        <x-icon category="Arrow" name="Arrow_Reload_02" class="w-5 h-5"/>
                                    </button>
                                </form>

                                <form method="POST"
                                      action="{{ route('staff.orders.items.destroy', ['locale' => $locale, 'order' => $order, 'item' => $item]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            aria-label="{{ __('staff-orders-item-delete-button') }}"
                                            class="px-3 py-1 rounded-full border border-red-500/70 text-red-300 hover:bg-red-500/20 hover:text-red-100 transition">
                                        <x-icon category="Interface" name="Trash_Empty" class="w-5 h-5"/>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-white/60">
                        {{ __('staff-orders-items-empty') }}
                    </p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/5 p-4 space-y-5 min-w-0">
            <div>
                <h2 class="text-xs uppercase tracking-[0.22em] text-white/60 mb-3 inline-flex items-center gap-2">
                    <x-icon category="Interface" name="Chart_Pie" class="w-5 h-5"/>
                    {{ __('staff-orders-summary-title') }}
                </h2>

                {{-- Детали позиций --}}
                @php
                    $promoDetails = $order->promoCode ? $order->promoCode->getDiscountDetailsForOrder($order) : null;
                    $eligibleItemIds = $promoDetails ? collect($promoDetails['eligible_items'])->pluck('id')->toArray() : [];
                @endphp

                <div class="space-y-2 mb-4 text-xs">
                    @foreach($order->items as $item)
                        @php
                            $isEligible = in_array($item->id, $eligibleItemIds);
                        @endphp
                        <div class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg {{ $isEligible ? 'bg-emerald-500/10 border border-emerald-500/30' : 'bg-black/20' }}">
                            <div class="flex-1 min-w-0">
                                <div class="text-white/90 text-xs truncate">
                                    {{ $item->menuItem->name[app()->getLocale()] ?? '' }}
                                </div>
                                <div class="text-white/50 text-[11px]">
                                    {{ number_format($item->unit_price, 2, '.', ' ') }} € × {{ $item->quantity }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <div class="text-right">
                                    <div class="text-white/90 text-xs font-medium">
                                        {{ number_format($item->total_price, 2, '.', ' ') }} €
                                    </div>
                                    @if($isEligible)
                                        <div class="text-emerald-300 text-[10px]">✓ {{ __('staff-orders-with-discount') }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="h-px bg-white/10 mb-3"></div>

                <div class="space-y-1 text-xs text-white/80">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-white/70">{{ __('staff-orders-summary-subtotal') }}</span>
                        <span class="text-white/90">{{ number_format($order->subtotal, 2, '.', ' ') }} €</span>
                    </div>

                    @if($order->promoCode && $order->discount_total > 0)
                        @php
                            $promoDetails = $order->promoCode->getDiscountDetailsForOrder($order);
                        @endphp

                        <div class="flex items-start justify-between gap-3 text-rose-300">
                            <div class="flex-1 min-w-0">
                                <div class="text-white/70 text-[11px]">{{ __('staff-orders-summary-promo-discount') }}</div>
                                @if($promoDetails['scope'] === 'all')
                                    <div class="text-[10px] text-white/50">
                                        {{ __('staff-orders-promo-scope-all-desc') }}
                                    </div>
                                @elseif($promoDetails['scope'] === 'category')
                                    <div class="text-[10px] text-white/50">
                                        {{ __('staff-orders-promo-scope-category-desc') }}:
                                        {{ $promoDetails['category_name'][app()->getLocale()] ?? '' }}
                                    </div>
                                    <div class="text-[10px] text-white/40">
                                        {{ __('staff-orders-promo-eligible-amount') }}: {{ number_format($promoDetails['eligible_amount'], 2, '.', ' ') }} €
                                    </div>
                                @elseif($promoDetails['scope'] === 'item')
                                    <div class="text-[10px] text-white/50">
                                        {{ __('staff-orders-promo-scope-item-desc') }}:
                                        {{ $promoDetails['item_name'][app()->getLocale()] ?? '' }}
                                    </div>
                                    <div class="text-[10px] text-white/40">
                                        {{ __('staff-orders-promo-eligible-amount') }}: {{ number_format($promoDetails['eligible_amount'], 2, '.', ' ') }} €
                                    </div>
                                @endif
                                @if($promoDetails['discount_type'] === 'percent')
                                    <div class="text-[10px] text-white/40">
                                        {{ __('staff-orders-promo-discount-percent', ['value' => $promoDetails['discount_value']]) }}
                                    </div>
                                @else
                                    <div class="text-[10px] text-white/40">
                                        {{ __('staff-orders-promo-discount-fixed', ['value' => number_format($promoDetails['discount_value'], 2, '.', ' ')]) }}
                                    </div>
                                @endif
                            </div>
                            <span class="shrink-0">-{{ number_format($promoDetails['discount_amount'], 2, '.', ' ') }} €</span>
                        </div>
                    @endif

                    @if(($loyaltySpent ?? 0) > 0)
                        <div class="flex items-center justify-between gap-3 text-rose-300">
                            <div class="text-white/70 text-[11px]">{{ __('staff-orders-summary-loyalty-discount') }}</div>
                            <span class="shrink-0">-{{ number_format($loyaltySpent, 2, '.', ' ') }} €</span>
                        </div>
                    @endif

                    @if(!($order->promoCode) && !($loyaltySpent ?? 0) > 0 && $order->discount_total > 0)
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-white/70">{{ __('staff-orders-summary-discount') }}</span>
                            <span class="text-white/90">-{{ number_format($order->discount_total, 2, '.', ' ') }} €</span>
                        </div>
                    @endif

                    <div class="flex items-center justify-between gap-3 text-sm font-semibold mt-1 pt-2 border-t border-white/10">
                        <span>{{ __('staff-orders-summary-total') }}</span>
                        <span>{{ number_format($order->total, 2, '.', ' ') }} €</span>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-[11px] uppercase tracking-[0.22em] text-white/60 mb-2 inline-flex items-center gap-2">
                    <x-icon category="User" name="User_01" class="w-5 h-5"/>
                    {{ __('staff-orders-client-title') }}
                </h3>

                <div class="text-xs text-white/80">
                    @if($order->user)
                        <div class="text-white">{{ $order->user->name }}</div>
                        <div class="text-white/60 text-[11px]">ID: {{ $order->user->id }}</div>

                        <div class="text-white/60 text-[11px] mt-1">
                            {{ __('staff-orders-client-loyalty-balance') }}:
                            {{ number_format($order->user->loyalty_balance, 2, '.', ' ') }} €
                        </div>

                        @if($order->status === \App\Models\Order::STATUS_OPEN)
                            <form method="POST"
                                  action="{{ route('staff.orders.apply-loyalty', ['locale' => $locale, 'order' => $order]) }}"
                                  class="mt-3 space-y-2 text-xs">
                                @csrf

                                <label class="block text-[11px] uppercase tracking-[0.22em] text-white/60">
                                    {{ __('staff-orders-loyalty-withdraw-label') }}
                                </label>

                                    <input type="number"
                                        name="amount"
                                        id="loyalty_amount_input"
                                        step="0.01"
                                        min="0.01"
                                        max="{{ number_format(min($order->user->loyalty_balance ?? 0, $order->total ?? 0), 2, '.', '') }}"
                                        data-max-allowed="{{ number_format(min($order->user->loyalty_balance ?? 0, $order->total ?? 0), 2, '.', '') }}"
                                        class="w-full bg-black/60 border border-white/20 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:ring-1 focus:ring-white/70"
                                        placeholder="{{ __('staff-orders-loyalty-amount-placeholder') }}">

                                <div class="flex flex-col gap-2">
                                        <button type="button"
                                            id="loyalty_use_all_btn"
                                            class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-full
                                                   border border-white/40 text-[11px] uppercase tracking-[0.18em] text-white
                                                   hover:bg-white hover:text-black transition">
                                        <x-icon category="Interface" name="Magic_Wand" class="w-5 h-5"/>
                                        {{ __('staff-orders-loyalty-use-all') }}
                                    </button>

                                    <button type="submit"
                                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full
                                                   border border-emerald-500/70 text-[11px] uppercase tracking-[0.22em]
                                                   text-emerald-300 hover:bg-emerald-500/20 hover:text-emerald-100 transition">
                                        <x-icon category="Interface" name="Check_Big" class="w-5 h-5"/>
                                        {{ __('staff-orders-loyalty-apply') }}
                                    </button>
                                </div>

                                <p class="text-[11px] text-white/40">
                                    {{ __('staff-orders-loyalty-hint') }}
                                </p>
                            </form>
                        @endif
                    @else
                        <div class="text-white/60">
                            {{ __('staff-orders-client-empty') }}
                        </div>
                    @endif
                </div>

                @if(!$order->user && $order->status === \App\Models\Order::STATUS_OPEN)
                    <form id="attachClientForm"
                          method="POST"
                          action="{{ route('staff.orders.attach-client', ['locale' => $locale, 'order' => $order]) }}"
                          class="mt-4 space-y-2 text-xs">
                        @csrf

                        <label class="block text-[11px] uppercase tracking-[0.22em] text-white/60">
                            {{ __('staff-orders-attach-client-field') }}
                        </label>

                        <input type="text"
                               id="attach_client_code"
                               name="loyalty_code"
                               class="w-full bg-black/60 border border-white/20 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:ring-1 focus:ring-white/70"
                               placeholder="{{ __('staff-orders-attach-client-placeholder') }}">

                        <div class="flex flex-col gap-2">
                            <button type="button"
                                    id="js-scan-qr-btn"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full
                                           border border-sky-400/70 text-[11px] uppercase tracking-[0.22em] text-sky-200
                                           hover:bg-sky-500 hover:text-black transition">
                                <x-icon category="System" name="Qr_Code" class="w-5 h-5"/>
                                {{ __('staff-orders-attach-client-scan-button') }}
                            </button>

                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full
                                           border border-white/70 text-[11px] uppercase tracking-[0.22em] text-white
                                           hover:bg-white hover:text-black transition">
                                <x-icon category="Interface" name="Link" class="w-5 h-5"/>
                                {{ __('staff-orders-attach-client-submit') }}
                            </button>
                        </div>

                        <p class="text-[11px] text-white/40">
                            {{ __('staff-orders-attach-client-hint') }}
                        </p>
                    </form>
                @endif
            </div>

            <div>
                <h3 class="text-[11px] uppercase tracking-[0.22em] text-white/60 mb-2 inline-flex items-center gap-2">
                    <x-icon category="Interface" name="Ticket_Voucher" class="w-5 h-5"/>
                    {{ __('staff-orders-promo-title') }}
                </h3>

                @if($order->promoCode)
                    <div class="text-xs text-white/80">
                        <div>
                            {{ __('staff-orders-promo-code-label') }}:
                            {{ $order->promoCode->public_code ?? (\Illuminate\Support\Str::substr($order->promoCode->qr_code, 0, 8) ?? '') }}
                        </div>
                        <div class="text-white/60 text-[11px]">
                            {{ $order->promoCode->name }}
                        </div>
                    </div>
                @elseif($order->status === \App\Models\Order::STATUS_OPEN)
                    @php
                        $promoHint = __('staff-orders-promo-hint');
                        if ($promoHint === 'staff-orders-promo-hint') {
                            $promoHint = __('staff-orders-attach-client-hint');
                        }
                    @endphp

                    <form id="promoApplyForm"
                          method="POST"
                          action="{{ route('staff.orders.apply-promo', ['locale' => $locale, 'order' => $order]) }}"
                          class="space-y-2 text-xs min-w-0">
                        @csrf

                        <label class="block text-[11px] uppercase tracking-[0.22em] text-white/60">
                            {{ __('staff-orders-promo-field') }}
                        </label>

                        <input type="text"
                               id="promo_code_input"
                               name="code"
                               class="w-full bg-black/60 border border-white/20 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:ring-1 focus:ring-white/70"
                               placeholder="{{ __('staff-orders-promo-placeholder') }}">

                        <div class="flex flex-col gap-2">
                            <button type="button"
                                    id="js-scan-promo-btn"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full
                                           border border-sky-400/70 text-[11px] uppercase tracking-[0.22em] text-sky-200
                                           hover:bg-sky-500 hover:text-black transition">
                                <x-icon category="System" name="Qr_Code" class="w-5 h-5"/>
                                {{ __('staff-orders-promo-scan-button') }}
                            </button>

                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full
                                           border border-white/70 text-[11px] uppercase tracking-[0.22em] text-white
                                           hover:bg-white hover:text-black transition">
                                <x-icon category="Interface" name="Check_Big" class="w-5 h-5"/>
                                {{ __('staff-orders-promo-submit') }}
                            </button>
                        </div>

                        <p class="text-[11px] text-white/40">
                            {{ $promoHint }}
                        </p>
                    </form>
                @else
                    <p class="text-xs text-white/60">
                        {{ __('staff-orders-promo-locked') }}
                    </p>
                @endif
            </div>

            @if($order->status === \App\Models\Order::STATUS_OPEN)
                <div>
                    <form method="POST" action="{{ route('staff.orders.complete', ['locale' => $locale, 'order' => $order]) }}" class="w-full">
                        @csrf
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-3 px-4 py-3 rounded-2xl
                                       border border-emerald-500/60 bg-emerald-500/15 text-emerald-100 text-sm font-semibold
                                       hover:bg-emerald-500/25 hover:border-emerald-400 hover:text-white transition">
                            <x-icon category="Interface" name="Checkbox_Check" class="w-5 h-5"/>
                            <span class="tracking-[0.18em] uppercase">{{ __('staff-orders-complete-button') }}</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <div id="qrScanModal" class="fixed inset-0 bg-black/90 hidden z-50">
        <div class="w-full h-full flex flex-col"
             style="
               padding-left: calc(1rem + env(safe-area-inset-left));
               padding-right: calc(1rem + env(safe-area-inset-right));
               padding-top: calc(1.25rem + env(safe-area-inset-top));
               padding-bottom: calc(1.5rem + env(safe-area-inset-bottom));
             ">

            <p id="qrModalHint" class="text-[11px] text-white/60 mb-3">
                {{ __('staff-orders-attach-client-scan-hint') }}
            </p>

            <div class="flex-1 flex items-center justify-center">
                <div class="w-full max-w-[520px]">
                    <div class="flex items-center justify-between px-1 pb-2">
                        <span id="qrModalTitle" class="text-[11px] uppercase tracking-[0.22em] text-white/70">
                            {{ __('staff-orders-attach-client-scan-title') }}
                        </span>
                        <button type="button"
                                id="js-close-qr-modal"
                                class="h-9 w-9 flex items-center justify-center rounded-full border border-white/30 text-white/80 bg-white/10 hover:bg-white hover:text-black transition">
                            ×
                        </button>
                    </div>
                    <div class="relative w-full" style="aspect-ratio: 1 / 1;">
                        <div class="absolute inset-0 rounded-2xl overflow-hidden border border-white/15 bg-black/60">
                        <video id="qr-video" playsinline muted class="absolute inset-0 w-full h-full object-cover"></video>
                        <div id="qr-overlay" class="pointer-events-none absolute inset-0 bg-black/70 opacity-0" style="transition: opacity 260ms ease-out; clip-path: none;"></div>
                        <div id="qr-focus-box"
                             class="pointer-events-none absolute border-2 border-[#f472b6] shadow-[0_0_0_4px_rgba(244,114,182,0.38)] rounded-xl opacity-0"
                             style="transform: translate(0, 0); width: 0; height: 0;"></div>
                        <div class="pointer-events-none absolute inset-[10%] rounded-2xl border border-white/25"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/@zxing/library@latest"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('qrScanModal');
    const closeBtn = document.getElementById('js-close-qr-modal');
    const videoEl = document.getElementById('qr-video');

    const scanLoyaltyBtn = document.getElementById('js-scan-qr-btn');
    const loyaltyInput = document.getElementById('attach_client_code');
    const loyaltyForm = document.getElementById('attachClientForm');

    const scanPromoBtn = document.getElementById('js-scan-promo-btn');
    const promoInput = document.getElementById('promo_code_input');
    const promoForm = document.getElementById('promoApplyForm');

    const focusBox = document.getElementById('qr-focus-box');
    const overlay = document.getElementById('qr-overlay');

    if (focusBox) {
        focusBox.style.transition = [
            'transform 480ms cubic-bezier(0.19, 1, 0.22, 1)',
            'width 480ms cubic-bezier(0.19, 1, 0.22, 1)',
            'height 480ms cubic-bezier(0.19, 1, 0.22, 1)',
            'opacity 260ms ease-out',
            'box-shadow 480ms ease'
        ].join(', ');
    }

    const titleEl = document.getElementById('qrModalTitle');
    const hintEl = document.getElementById('qrModalHint');

    // Loyalty: use all balance button logic
    const loyaltyUseAllBtn = document.getElementById('loyalty_use_all_btn');
    const loyaltyAmountInput = document.getElementById('loyalty_amount_input');
    if (loyaltyUseAllBtn && loyaltyAmountInput) {
        loyaltyUseAllBtn.addEventListener('click', () => {
            const maxAllowed = parseFloat(loyaltyAmountInput.getAttribute('data-max-allowed') || loyaltyAmountInput.max || '0');
            if (!isNaN(maxAllowed) && maxAllowed > 0) {
                // Set with 2 decimals, normalize dot as decimal separator
                loyaltyAmountInput.value = (Math.floor(maxAllowed * 100) / 100).toFixed(2);
                loyaltyAmountInput.dispatchEvent(new Event('input', { bubbles: true }));
                loyaltyAmountInput.focus();
            }
        });
    }

    let controls = null;
    let onDecodeFn = null;
    let scanning = false;
    let closeTimer = null;
    let lastPointTs = 0;
    let latestPoints = [];

    function clamp(val, min, max) {
        return Math.min(Math.max(val, min), max);
    }

    function normalize(text) {
        if (text == null) return '';
        let t = String(text).replace(/\u0000/g, '').trim();
        t = t.replace(/^[\s"'“”„«»]+/, '').replace(/[\s"'“”„«»]+$/, '');
        return t;
    }

    function openModal(customTitle, customHint) {
        if (titleEl && customTitle) titleEl.textContent = customTitle;
        if (hintEl && customHint) hintEl.textContent = customHint;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function stopAll() {
        if (closeTimer) {
            clearTimeout(closeTimer);
            closeTimer = null;
        }
        lastPointTs = 0;
        try { if (controls) controls.stop(); } catch (e) {}
        controls = null;
        try { if (videoEl) videoEl.srcObject = null; } catch (e) {}
        scanning = false;
        onDecodeFn = null;
        latestPoints = [];
        hideFocusBox();
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        stopAll();
    }

    function done(text) {
        const fn = onDecodeFn;
        closeModal();
        if (typeof fn === 'function') fn(text);
    }

    function moveFocusBox(points) {
        if (!focusBox || !videoEl || !points || !points.length) return;

        const videoW = videoEl.videoWidth || 0;
        const videoH = videoEl.videoHeight || 0;
        const clientW = videoEl.clientWidth || 0;
        const clientH = videoEl.clientHeight || 0;
        if (!videoW || !videoH || !clientW || !clientH) return;

        const xs = points.map(p => (typeof p.getX === 'function' ? p.getX() : p?.x) || 0);
        const ys = points.map(p => (typeof p.getY === 'function' ? p.getY() : p?.y) || 0);

        let minX = Math.min(...xs);
        let maxX = Math.max(...xs);
        let minY = Math.min(...ys);
        let maxY = Math.max(...ys);

        const padRatio = 0.035; // larger padding so box fully covers and slightly surrounds QR
        const padX = videoW * padRatio;
        const padY = videoH * padRatio;

        minX = clamp(minX - padX, 0, videoW);
        maxX = clamp(maxX + padX, 0, videoW);
        minY = clamp(minY - padY, 0, videoH);
        maxY = clamp(maxY + padY, 0, videoH);

        // Account for object-fit: cover on the video element.
        const scale = Math.max(clientW / videoW, clientH / videoH);
        const displayedW = videoW * scale;
        const displayedH = videoH * scale;
        const offsetX = (clientW - displayedW) / 2;
        const offsetY = (clientH - displayedH) / 2;

        const left = offsetX + minX * scale;
        const top = offsetY + minY * scale;
        const width = (maxX - minX) * scale;
        const height = (maxY - minY) * scale;

        focusBox.style.opacity = '1';
        focusBox.style.width = `${width}px`;
        focusBox.style.height = `${height}px`;
        focusBox.style.transform = `translate(${left}px, ${top}px)`;

        // Update overlay to darken everything except QR area
        if (overlay) {
            overlay.style.opacity = '1';
            const right = left + width;
            const bottom = top + height;
            // Use evenodd fill-rule to create a cutout
            overlay.style.clipPath = `polygon(evenodd, 0% 0%, 100% 0%, 100% 100%, 0% 100%, 0% 0%, ${left}px ${top}px, ${left}px ${bottom}px, ${right}px ${bottom}px, ${right}px ${top}px, ${left}px ${top}px)`;
        }
    }

    function hideFocusBox() {
        if (!focusBox) return;
        focusBox.style.opacity = '0';
        focusBox.style.width = '0';
        focusBox.style.height = '0';
        focusBox.style.transform = 'translate(0px, 0px)';
        if (overlay) {
            overlay.style.opacity = '0';
            overlay.style.clipPath = 'none';
        }
        latestPoints = [];
    }

    function extractPoints(result, err) {
        if (result?.getResultPoints) {
            const pts = result.getResultPoints();
            if (pts && pts.length) return pts;
        }
        const errPoints = err?.resultPoints || err?.getResultPoints?.();
        if (Array.isArray(errPoints) && errPoints.length) return errPoints;
        return null;
    }

    function makeReader() {
        const hints = new Map();
        hints.set(ZXing.DecodeHintType.TRY_HARDER, true);
        hints.set(ZXing.DecodeHintType.ALSO_INVERTED, true);
        hints.set(ZXing.DecodeHintType.POSSIBLE_FORMATS, [ZXing.BarcodeFormat.QR_CODE]);
        hints.set(ZXing.DecodeHintType.NEED_RESULT_POINT_CALLBACK, (point) => {
            if (!point) return;
            const x = typeof point.getX === 'function' ? point.getX() : point?.x;
            const y = typeof point.getY === 'function' ? point.getY() : point?.y;
            if (x == null || y == null) return;
            latestPoints.push({ x, y });
            if (latestPoints.length > 24) latestPoints.shift();
            // Show overlay immediately when we detect any QR finder pattern points
            if (latestPoints.length >= 1) {
                lastPointTs = Date.now();
                moveFocusBox(latestPoints);
            }
        });
        return new ZXing.BrowserQRCodeReader(hints, 90);
    }

    async function startScan(callback, customTitle, customHint) {
        if (scanning) return;
        scanning = true;
        onDecodeFn = callback;

        openModal(customTitle, customHint);

        await new Promise(r => requestAnimationFrame(() => setTimeout(r, 80)));

        try {
            stopAll();
            scanning = true;
            onDecodeFn = callback;

            const reader = makeReader();

            controls = await reader.decodeFromConstraints(
                {
                    audio: false,
                    video: {
                        facingMode: { ideal: 'environment' },
                        width: { ideal: 2560 },
                        height: { ideal: 1440 }
                    }
                },
                videoEl,
                (result, err) => {
                    if (!scanning) return;

                    const points = extractPoints(result, err);
                    if (points && points.length) {
                        lastPointTs = Date.now();
                        moveFocusBox(points);
                    } else if (lastPointTs && Date.now() - lastPointTs > 200) {
                        hideFocusBox();
                    }

                    if (result && result.getText) {
                        const t = normalize(result.getText());
                        if (t) {
                            scanning = false;
                            closeTimer = setTimeout(() => done(t), 450);
                        }
                    }
                }
            );
        } catch (e) {
            alert(@json(__('staff-orders-attach-client-scan-error')) + '\n' + (e?.name ? `${e.name}: ` : '') + (e?.message || e));
            closeModal();
        }
    }

    if (scanLoyaltyBtn) {
        scanLoyaltyBtn.addEventListener('click', () => {
            startScan((text) => {
                if (loyaltyInput) loyaltyInput.value = text;
                if (loyaltyForm) loyaltyForm.submit();
            },
            @json(__('staff-orders-attach-client-scan-title')),
            @json(__('staff-orders-attach-client-scan-hint')));
        });
    }

    if (scanPromoBtn) {
        scanPromoBtn.addEventListener('click', () => {
            startScan((text) => {
                if (promoInput) promoInput.value = text;
                if (promoForm) promoForm.submit();
            },
            @json(__('staff-orders-promo-title')),
            @json(__('staff-orders-attach-client-scan-hint')));
        });
    }

    if (closeBtn) closeBtn.addEventListener('click', closeModal);

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
    }
});
</script>
@endpush
