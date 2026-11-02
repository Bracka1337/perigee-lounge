@extends('layouts.admin')

@section('content')
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="logo-font text-2xl md:text-3xl lg:text-4xl mb-2">
                {{ __('admin-users-show-title') }}
            </h1>
            <p class="text-xs md:text-sm text-ink-muted">
                {{ __('admin-users-show-viewing') }}: {{ $user->name ?? $user->email }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.users.index') }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full border border-line
                      text-xs md:text-xs text-ink-soft hover:text-ink
                      hover:border-line-strong hover:bg-surface transition">
                {{ __('admin-users-show-back') }}
            </a>
            <a href="{{ route('admin.users.edit', $user) }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full border border-line
                      text-xs md:text-xs text-ink-soft hover:text-ink
                      hover:border-line-strong hover:bg-surface transition">
                {{ __('admin-users-show-edit') }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <div class="rounded-2xl bg-ground/40 border border-line p-4">
            <div class="text-xs text-ink-muted">{{ __('admin-users-show-role') }}</div>
            <div class="text-lg font-semibold">
                @if($user->isAdmin()) {{ __('admin-users-role-label-admin') }} @elseif($user->isWorker()) {{ __('admin-users-role-label-staff') }} @else {{ __('admin-users-role-label-client') }} @endif
            </div>
        </div>
        <div class="rounded-2xl bg-ground/40 border border-line p-4">
            <div class="text-xs text-ink-muted">{{ __('admin-users-show-loyalty-balance') }}</div>
            <div class="text-lg font-semibold">{{ number_format($stats['loyalty_balance'] ?? 0, 2, '.', ' ') }}</div>
            @if(!empty($user->loyalty_code))
                <div class="mt-1 text-xs text-ink-muted">{{ __('admin-users-show-loyalty-code') }}: {{ $user->loyalty_code }}</div>
            @endif
        </div>
        <div class="rounded-2xl bg-ground/40 border border-line p-4">
            <div class="text-xs text-ink-muted">{{ __('admin-users-show-orders-count') }}</div>
            <div class="text-lg font-semibold">{{ $stats['orders_count'] ?? 0 }}</div>
        </div>
        <div class="rounded-2xl bg-ground/40 border border-line p-4">
            <div class="text-xs text-ink-muted">{{ __('admin-users-show-completed-total') }}</div>
            <div class="text-lg font-semibold">{{ number_format($stats['completed_total'] ?? 0, 2, '.', ' ') }}</div>
        </div>
        <div class="rounded-2xl bg-ground/40 border border-line p-4">
            <div class="text-xs text-ink-muted">{{ __('admin-users-show-loyalty-earned') }}</div>
            <div class="text-lg font-semibold">{{ number_format($stats['loyalty_earned'] ?? 0, 2, '.', ' ') }}</div>
        </div>
        <div class="rounded-2xl bg-ground/40 border border-line p-4">
            <div class="text-xs text-ink-muted">{{ __('admin-users-show-loyalty-redeemed') }}</div>
            <div class="text-lg font-semibold">{{ number_format($stats['loyalty_redeemed'] ?? 0, 2, '.', ' ') }}</div>
        </div>
        <div class="rounded-2xl bg-ground/40 border border-line p-4">
            <div class="text-xs text-ink-muted">{{ __('admin-users-show-promo-claimed') }}</div>
            <div class="text-lg font-semibold">{{ $stats['promo_claimed'] ?? 0 }}</div>
        </div>
        <div class="rounded-2xl bg-ground/40 border border-line p-4">
            <div class="text-xs text-ink-muted">{{ __('admin-users-show-promo-used') }}</div>
            <div class="text-lg font-semibold">{{ $stats['promo_used'] ?? 0 }}</div>
        </div>
        <div class="rounded-2xl bg-ground/40 border border-line p-4">
            <div class="text-xs text-ink-muted">{{ __('admin-users-show-promo-discount-total') }}</div>
            <div class="text-lg font-semibold">{{ number_format($stats['promo_discount_total'] ?? 0, 2, '.', ' ') }}</div>
        </div>
    </div>

    <div class="space-y-8">
        <div>
            <h2 class="text-xl font-semibold mb-3">{{ __('admin-users-show-loyalty-history-title') }}</h2>
            <div class="rounded-2xl bg-ground/40 border border-line overflow-hidden">
                <div class="divide-y divide-line">
                    @forelse($loyaltyEarnings as $entry)
                        <div class="px-4 md:px-5 py-3 flex items-center justify-between">
                            <div class="text-sm">
                                <span class="text-ink-muted">{{ $entry->created_at?->format('d.m.Y H:i') }}</span>
                                <span class="text-ink-muted">•</span>
                                <span>
                                    @if($entry->amount > 0)
                                        {{ __('admin-users-show-loyalty-credit') }}
                                    @else
                                        {{ __('admin-users-show-loyalty-debit') }}
                                    @endif
                                </span>
                                @if($entry->order)
                                    <span class="text-ink-muted">•</span>
                                    <span>{{ __('admin-users-show-order') }} #{{ $entry->order->id }}</span>
                                @endif
                            </div>
                            <div class="text-sm font-semibold {{ $entry->amount > 0 ? 'text-emerald-300' : 'text-red-300' }}">
                                {{ $entry->amount > 0 ? '+' : '' }}{{ number_format($entry->amount, 2, '.', ' ') }}
                            </div>
                        </div>
                    @empty
                        <div class="px-4 md:px-5 py-6 text-center text-ink-muted">{{ __('admin-users-show-loyalty-empty') }}</div>
                    @endforelse
                </div>
                @if($loyaltyEarnings->hasPages())
                    <div class="px-4 md:px-5 py-3 bg-ground/60">{{ $loyaltyEarnings->links() }}</div>
                @endif
            </div>
        </div>

        <div>
            <h2 class="text-xl font-semibold mb-3">{{ __('admin-users-show-promo-claimed-title') }}</h2>
            <div class="rounded-2xl bg-ground/40 border border-line p-4 space-y-3">
                @forelse($claimedPromoCodes as $promo)
                    <div class="flex items-center justify-between">
                        <div class="text-sm">
                            <span class="font-semibold">{{ $promo->public_code }}</span>
                            @if($promo->description)
                                <span class="text-ink-muted">— {{ $promo->description }}</span>
                            @endif
                            @if($promo->pivot?->claimed_at)
                                <span class="text-ink-muted">•</span>
                                <span class="text-ink-muted">{{ __('admin-users-show-promo-claimed-at') }}: {{ \Illuminate\Support\Carbon::parse($promo->pivot->claimed_at)->format('d.m.Y H:i') }}</span>
                            @endif
                            @if($promo->pivot?->is_used)
                                <span class="text-ink-muted">•</span>
                                <span class="text-emerald-300">{{ __('admin-users-show-promo-used-label') }}</span>
                            @endif
                        </div>
                        <div class="text-xs text-ink-muted">
                            {{ __('staff-promo-scope-'.$promo->scope) }}
                        </div>
                    </div>
                @empty
                    <div class="text-ink-muted text-sm">{{ __('admin-users-show-promo-claimed-empty') }}</div>
                @endforelse
            </div>

            <h3 class="text-lg font-semibold mt-6 mb-2">{{ __('admin-users-show-promo-usage-title') }}</h3>
            <div class="rounded-2xl bg-ground/40 border border-line overflow-hidden">
                <div class="divide-y divide-line">
                    @forelse($promoUsages as $usage)
                        <div class="px-4 md:px-5 py-3 flex items-center justify-between">
                            <div class="text-sm">
                                <span class="text-ink-muted">{{ $usage->created_at?->format('d.m.Y H:i') }}</span>
                                <span class="text-ink-muted">•</span>
                                <span>{{ __('admin-users-show-promo') }} {{ $usage->promoCode?->public_code }}</span>
                                @if($usage->order)
                                    <span class="text-ink-muted">•</span>
                                    <span>{{ __('admin-users-show-order') }} #{{ $usage->order->id }}</span>
                                @endif
                            </div>
                            <div class="text-sm font-semibold text-emerald-300">
                                −{{ number_format($usage->discount_amount, 2, '.', ' ') }}
                            </div>
                        </div>
                    @empty
                        <div class="px-4 md:px-5 py-6 text-center text-ink-muted">{{ __('admin-users-show-promo-usage-empty') }}</div>
                    @endforelse
                </div>
                @if($promoUsages->hasPages())
                    <div class="px-4 md:px-5 py-3 bg-ground/60">{{ $promoUsages->links() }}</div>
                @endif
            </div>
        </div>

        <div>
            <h2 class="text-xl font-semibold mb-3">{{ __('admin-users-show-orders-title') }}</h2>
            <div class="rounded-2xl bg-ground/40 border border-line overflow-hidden">
                <div class="divide-y divide-line">
                    @forelse($orders as $order)
                        <div class="px-4 md:px-5 py-3 flex items-center justify-between">
                            <div class="text-sm">
                                <span class="font-semibold">#{{ $order->id }}</span>
                                <span class="text-ink-muted">•</span>
                                <span>{{ ucfirst($order->status) }}</span>
                                @if($order->started_at)
                                    <span class="text-ink-muted">•</span>
                                    <span>{{ __('admin-users-show-order-started') }}: {{ $order->started_at->format('d.m.Y H:i') }}</span>
                                @endif
                                @if($order->completed_at)
                                    <span class="text-ink-muted">•</span>
                                    <span>{{ __('admin-users-show-order-completed') }}: {{ $order->completed_at->format('d.m.Y H:i') }}</span>
                                @endif
                                @if($order->table)
                                    <span class="text-ink-muted">•</span>
                                    <span>{{ __('admin-users-show-order-table') }}: {{ $order->table->name ?? $order->table->id }}</span>
                                @endif
                            </div>
                            <div class="text-sm font-semibold">
                                <span class="text-ink-muted">{{ __('admin-users-show-order-total') }}</span>
                                <span class="ml-2">{{ number_format($order->total ?? 0, 2, '.', ' ') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 md:px-5 py-6 text-center text-ink-muted">{{ __('admin-users-show-orders-empty') }}</div>
                    @endforelse
                </div>
                @if($orders->hasPages())
                    <div class="px-4 md:px-5 py-3 bg-ground/60">{{ $orders->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
