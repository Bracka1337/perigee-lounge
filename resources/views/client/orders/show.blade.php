@extends('layouts.client')

@section('client-header')
    <div class="mb-10">
        <p class="text-xs tracking-[0.35em] uppercase text-white/60 mb-3">
            {{ __('client-orders-badge') }}
        </p>
        <h1 class="logo-font text-3xl md:text-4xl mb-2 drop-shadow-[0_0_10px_rgba(255,255,255,0.7)]">
            {{ __('client-orders-title') }}
        </h1>
        <p class="text-sm md:text-base text-white/70">
            {{ __('client-orders-subtitle') }}
        </p>
    </div>
@endsection

@section('client-content')
    <div class="w-full">
        <a href="{{ route('client.orders.index', ['locale' => app()->getLocale()]) }}"
           class="inline-flex items-center text-xs text-white/70 hover:text-white transition-colors mb-6"
           title="{{ __('client-orders-back-tooltip') }}"
           aria-label="{{ __('client-orders-back-tooltip') }}">
            <img src="{{ asset('icons/coolicons/Arrow/Chevron_Left_MD.svg') }}" alt="{{ __('client-orders-back-tooltip') }}" class="h-5 w-5 opacity-90 invert" />
            <span class="ml-2">{{ __('client-orders-back-to-list') }}</span>
        </a>

        <div class="rounded-3xl border border-white/10 bg-white/5 p-6 md:p-8 shadow-xl shadow-black/60 text-xs text-white/80">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-3">
                <div>
                    <div class="text-[11px] text-white/60">
                        #{{ $order->id }} · {{ $order->created_at?->format('d.m.Y H:i') }}
                    </div>
                    <div class="text-sm text-white">
                        {{ __('client-orders-table-label') }} {{ $order->table->name ?? '#' }}
                    </div>
                    <div class="text-[11px] text-white/60 mt-1">
                        {{ __('client-orders-status-label') }}:
                        {{ __('client-orders-status-'.$order->status) }}
                    </div>
                </div>
            </div>

            @if($order->items->isNotEmpty())
                @php
                    $promoDetails = $order->promoCode ? $order->promoCode->getDiscountDetailsForOrder($order) : null;
                    $eligibleItemIds = $promoDetails ? collect($promoDetails['eligible_items'])->pluck('id')->toArray() : [];
                @endphp

                <div class="mt-3 space-y-2">
                    @foreach($order->items as $item)
                        @php
                            $isEligible = in_array($item->id, $eligibleItemIds);
                        @endphp
                        <div class="flex items-start justify-between gap-3 px-2.5 py-2 rounded-lg {{ $isEligible ? 'bg-emerald-500/10 border border-emerald-500/30' : '' }}">
                            <div class="min-w-0 flex-1">
                                <div class="text-xs text-white truncate">
                                    {{ $item->menuItem->name[app()->getLocale()] ?? '' }}
                                </div>
                                <div class="text-[11px] text-white/60">
                                    {{ number_format($item->unit_price, 2, '.', ' ') }} € × {{ $item->quantity }}
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-xs text-white whitespace-nowrap">
                                    {{ number_format($item->total_price, 2, '.', ' ') }} €
                                </div>
                                @if($isEligible)
                                    <div class="text-emerald-300 text-[10px] whitespace-nowrap">✓ {{ __('client-orders-with-discount') }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-4 border-t border-white/10 pt-3">
                <div class="flex items-center justify-between pt-2">
                    <div class="text-[11px] text-white/60">{{ __('client-orders-total-label') }}</div>
                    <div class="text-base text-white">{{ number_format($order->total, 2, '.', ' ') }} €</div>
                </div>
            </div>

@if($order->table && $order->status !== \App\Models\Order::STATUS_COMPLETED && $order->status !== \App\Models\Order::STATUS_CANCELLED)
                <div class="mt-6">
                    <a href="{{ route('client.table.menu', ['locale' => app()->getLocale(), 'code' => $order->table->name]) }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full border border-white/60 bg-white/5 hover:bg-white/15 text-[11px] uppercase tracking-[0.18em] text-white/90 transition">
                        <img src="{{ asset('icons/coolicons/Interface/Shopping_Cart_01.svg') }}" alt="" class="w-4 h-4 invert opacity-80">
                        <span>{{ __('client-orders-order-again') }}</span>
                    </a>
                </div>
            @endif        </div>
    </div>
@endsection
