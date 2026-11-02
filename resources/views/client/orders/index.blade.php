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
    @if($orders->isEmpty())
        <p class="text-sm text-ink-muted">
            {{ __('client-orders-empty') }}
        </p>
    @else
        <div class="space-y-3">
            @foreach($orders as $order)
                <div class="rounded-2xl border border-line bg-surface p-4  text-xs text-ink-soft">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-xs text-ink-muted">
                                #{{ $order->id }} · {{ $order->created_at->format('d.m.Y H:i') }}
                            </div>
                            <div class="text-sm text-ink mt-0.5">
                                {{ __('client-orders-table-label') }} {{ $order->table->name ?? '#' }}
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <div class="text-xs text-ink-muted">{{ __('client-orders-total-label') }}</div>
                                <div class="text-base text-ink">{{ number_format($order->total, 2, '.', ' ') }} €</div>
                            </div>
                            <a href="{{ route('client.orders.show', ['locale' => app()->getLocale(), 'order' => $order]) }}"
                               class="shrink-0 inline-flex items-center justify-center rounded-full hover:bg-line transition-colors p-2"
                               title="{{ __('client-orders-view-tooltip') }}"
                               aria-label="{{ __('client-orders-view-tooltip') }}">
                                <img src="{{ asset('icons/coolicons/Interface/External_Link.svg') }}" alt="{{ __('client-orders-view-tooltip') }}" class="h-5 w-5 opacity-90 invert" />
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
