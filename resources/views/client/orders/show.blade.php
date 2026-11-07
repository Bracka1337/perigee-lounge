@extends('layouts.client')

@section('client-header')
    <div class="mb-10">
        <p class="text-xs text-ink-muted mb-3">
            {{ __('client-orders-badge') }}
        </p>
        <h1 class="logo-font text-3xl md:text-4xl mb-2">
            {{ __('client-orders-title') }}
        </h1>
        <p class="text-sm md:text-base text-ink-muted">
            {{ __('client-orders-subtitle') }}
        </p>
    </div>
@endsection

@section('client-content')
    @if (session('success'))
        <div role="status" class="mb-6 rounded-xl border border-emerald-500/60 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div role="alert" class="mb-6 rounded-xl border border-red-500/60 bg-red-500/10 px-4 py-3 text-sm text-red-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="w-full">
        <a href="{{ route('client.orders.index', ['locale' => app()->getLocale()]) }}"
           class="inline-flex items-center text-xs text-ink-muted hover:text-ink transition-colors mb-6"
           title="{{ __('client-orders-back-tooltip') }}"
           aria-label="{{ __('client-orders-back-tooltip') }}">
            <img src="{{ asset('icons/coolicons/Arrow/Chevron_Left_MD.svg') }}" alt="{{ __('client-orders-back-tooltip') }}" class="h-5 w-5 opacity-90 invert" />
            <span class="ml-2">{{ __('client-orders-back-to-list') }}</span>
        </a>

        <div class="rounded-2xl border border-line bg-surface p-6 md:p-8  text-xs text-ink-soft">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-3">
                <div>
                    <div class="text-xs text-ink-muted">
                        #{{ $order->id }} · {{ $order->created_at?->format('d.m.Y H:i') }}
                    </div>
                    <div class="text-sm text-ink">
                        {{ __('client-orders-table-label') }} {{ $order->table->name ?? '#' }}
                    </div>
                    <div class="text-xs text-ink-muted mt-1">
                        {{ __('client-orders-status-label') }}:
                        {{ __('client-orders-status-'.$order->status->value) }}
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
                                <div class="text-xs text-ink truncate">
                                    {{ $item->menuItem->name[app()->getLocale()] ?? '' }}
                                </div>
                                <div class="text-xs text-ink-muted">
                                    {{ number_format($item->unit_price, 2, '.', ' ') }} € × {{ $item->quantity }}
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-xs text-ink whitespace-nowrap">
                                    {{ number_format($item->total_price, 2, '.', ' ') }} €
                                </div>
                                @if($isEligible)
                                    <div class="text-emerald-300 text-xs whitespace-nowrap">✓ {{ __('client-orders-with-discount') }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-4 border-t border-line pt-3">
                <div class="flex items-center justify-between pt-2">
                    <div class="text-xs text-ink-muted">{{ __('client-orders-total-label') }}</div>
                    <div class="text-base text-ink">{{ number_format($order->total, 2, '.', ' ') }} €</div>
                </div>
            </div>

@if($order->table && $order->status !== \App\Enums\OrderStatus::Completed && $order->status !== \App\Enums\OrderStatus::Cancelled)
                <div class="mt-6">
                    <a href="{{ route('client.table.menu', ['locale' => app()->getLocale(), 'code' => $order->table->name]) }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full border border-line-strong bg-surface hover:bg-line text-xs text-ink-soft transition">
                        <img src="{{ asset('icons/coolicons/Interface/Shopping_Cart_01.svg') }}" alt="" class="w-4 h-4 invert opacity-80">
                        <span>{{ __('client-orders-order-again') }}</span>
                    </a>
                </div>
            @endif        </div>
    </div>
@endsection
