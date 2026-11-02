@extends('layouts.admin')

@section('content')
    @php
        $revLabels = ($revenueByDay ?? collect())->pluck('d')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d.m'))->values();
        $revValues = ($revenueByDay ?? collect())->pluck('sum')->map(fn($v) => (float)$v)->values();

        $ordLabels = ($ordersByDay ?? collect())->pluck('d')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d.m'))->values();
        $ordValues = ($ordersByDay ?? collect())->pluck('cnt')->map(fn($v) => (int)$v)->values();

        if ($revLabels->isEmpty()) { $revLabels = collect([now()->format('d.m')]); $revValues = collect([0]); }
        if ($ordLabels->isEmpty()) { $ordLabels = collect([now()->format('d.m')]); $ordValues = collect([0]); }

        $fmtMoney = fn($v) => number_format((float)$v, 2, '.', ' ') . ' €';
    @endphp

    <div class="mb-8">
        <h1 class="logo-font text-2xl md:text-3xl lg:text-4xl mb-2">
            {{ __('admin-dashboard-title') }}
        </h1>
        <p class="text-sm text-ink-muted">
            {{ __('admin-dashboard-content') }}
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
        <div class="p-4 rounded-2xl bg-surface border border-line">
            <div class="text-xs text-ink-muted">{{ __('admin-dashboard-revenue-today') }}</div>
            <div class="text-2xl text-ink mt-1">{{ $fmtMoney($revenueToday ?? 0) }}</div>
            <div class="text-xs text-ink-muted mt-1">
                7d: {{ $fmtMoney($revenue7 ?? 0) }} · 30d: {{ $fmtMoney($revenue30 ?? 0) }}
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-surface border border-line">
            <div class="text-xs text-ink-muted">{{ __('admin-dashboard-orders') }}</div>
            <div class="text-2xl text-ink mt-1">{{ (int)($ordersToday ?? 0) }}</div>
            <div class="text-xs text-ink-muted mt-1">
                7d: {{ (int)($orders7 ?? 0) }} · 30d: {{ (int)($orders30 ?? 0) }}
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-surface border border-line">
            <div class="text-xs text-ink-muted">{{ __('admin-dashboard-average-order-value') }}</div>
            <div class="text-2xl text-ink mt-1">{{ $fmtMoney($aov30 ?? 0) }}</div>
            <div class="text-xs text-ink-muted mt-1">
                {{ __('admin-dashboard-discounts-30d') }}: {{ $fmtMoney($discount30 ?? 0) }} · {{ __('admin-dashboard-promo-share') }}: {{ (float)($promoShare30 ?? 0) }}%
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-surface border border-line">
            <div class="text-xs text-ink-muted">{{ __('admin-dashboard-now') }}</div>

            <div class="mt-3 grid grid-cols-2 gap-3">
                <div class="rounded-xl border border-line bg-ground/30 p-3">
                    <div class="text-xs text-ink-muted">{{ __('admin-dashboard-active-orders') }}</div>
                    <div class="text-xl text-ink mt-1">{{ (int)($activeOrdersNow ?? 0) }}</div>
                </div>

                <div class="rounded-xl border border-line bg-ground/30 p-3">
                    <div class="text-xs text-ink-muted">{{ __('admin-dashboard-active-reservations') }}</div>
                    <div class="text-xl text-ink mt-1">{{ (int)($activeReservationsNow ?? 0) }}</div>
                </div>

                <div class="rounded-xl border border-line bg-ground/30 p-3">
                    <div class="text-xs text-ink-muted">{{ __('admin-dashboard-new-clients-30d') }}</div>
                    <div class="text-xl text-ink mt-1">{{ (int)($newClients30 ?? 0) }}</div>
                </div>

                <div class="rounded-xl border border-line bg-ground/30 p-3">
                    <div class="text-xs text-ink-muted">{{ __('admin-dashboard-loyalty-30d') }}</div>
                    <div class="text-xl text-ink mt-1">{{ number_format((float)($loyaltyEarned30 ?? 0), 2, '.', ' ') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-8">
        <div class="p-4 rounded-2xl bg-surface border border-line">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div>
                    <div class="text-xs text-ink-muted">{{ __('admin-dashboard-revenue-30d') }}</div>
                    <div class="text-sm text-ink-muted mt-1">{{ __('admin-dashboard-revenue-30d-subtitle') }}</div>
                </div>
                <div class="text-xs text-ink-muted">
                    {{ __('admin-dashboard-updated') }}: {{ now()->format('d.m.Y H:i') }}
                </div>
            </div>
            <div class="rounded-xl bg-ground/30 border border-line p-3">
                <canvas id="revenueChart" height="120"></canvas>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-surface border border-line">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div>
                    <div class="text-xs text-ink-muted">{{ __('admin-dashboard-orders-30d') }}</div>
                    <div class="text-sm text-ink-muted mt-1">{{ __('admin-dashboard-orders-30d-subtitle') }}</div>
                </div>
                <div class="text-xs text-ink-muted">
                    {{ __('admin-dashboard-status') }}: completed
                </div>
            </div>
            <div class="rounded-xl bg-ground/30 border border-line p-3">
                <canvas id="ordersChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="p-4 rounded-2xl bg-surface border border-line">
            <div class="flex items-center justify-between mb-3">
                <div class="text-xs text-ink-muted">{{ __('admin-dashboard-top-promos') }}</div>
                <div class="text-xs text-ink-muted">{{ __('admin-dashboard-top-promos-subtitle') }}</div>
            </div>

            <div class="overflow-hidden rounded-xl border border-line bg-ground/30">
                <table class="w-full text-xs">
                    <thead class="bg-surface">
                    <tr class="text-ink-muted">
                        <th class="px-3 py-2 text-left">{{ __('admin-dashboard-table-code') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('admin-dashboard-table-uses') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('admin-dashboard-table-discount') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse(($topPromos ?? collect()) as $row)
                        <tr class="border-t border-line text-ink-muted">
                            <td class="px-3 py-2">
                                {{ optional($row->promoCode)->public_code ?? (optional($row->promoCode)->qr_code ? substr(optional($row->promoCode)->qr_code, 0, 8) : ('#'.$row->promo_code_id)) }}
                            </td>
                            <td class="px-3 py-2 text-right">{{ (int)($row->uses ?? 0) }}</td>
                            <td class="px-3 py-2 text-right">{{ $fmtMoney($row->discount_sum ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-3 py-4 text-center text-ink-muted">
                                {{ __('admin-dashboard-no-data') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-surface border border-line">
            <div class="flex items-center justify-between mb-3">
                <div class="text-xs text-ink-muted">{{ __('admin-dashboard-top-clients') }}</div>
                <div class="text-xs text-ink-muted">{{ __('admin-dashboard-top-clients-subtitle') }}</div>
            </div>

            <div class="overflow-hidden rounded-xl border border-line bg-ground/30">
                <table class="w-full text-xs">
                    <thead class="bg-surface">
                    <tr class="text-ink-muted">
                        <th class="px-3 py-2 text-left">{{ __('admin-dashboard-table-client') }}</th>
                        <th class="px-3 py-2 text-left hidden sm:table-cell">{{ __('admin-dashboard-table-email') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('admin-dashboard-table-amount') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse(($topClients ?? collect()) as $u)
                        <tr class="border-t border-line text-ink-muted">
                            <td class="px-3 py-2">
                                <div class="text-ink">{{ $u->name ?? '—' }}</div>
                                <div class="text-xs text-ink-muted">ID: {{ $u->id }}</div>
                            </td>
                            <td class="px-3 py-2 hidden sm:table-cell text-ink-muted">
                                {{ $u->email ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-right">
                                {{ $fmtMoney($u->spent ?? 0) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-3 py-4 text-center text-ink-muted">
                                {{ __('admin-dashboard-no-data') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/vendor/chart.js')

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const revLabels = @json($revLabels);
                const revValues = @json($revValues);

                const ordLabels = @json($ordLabels);
                const ordValues = @json($ordValues);

                const revCtx = document.getElementById('revenueChart');
                if (revCtx) {
                    new Chart(revCtx, {
                        type: 'line',
                        data: {
                            labels: revLabels,
                            datasets: [{
                                label: 'Revenue (€)',
                                data: revValues,
                                tension: 0.35,
                                fill: false,
                                pointRadius: 2,
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => ` ${Number(ctx.raw).toFixed(2)} €`
                                    }
                                }
                            },
                            scales: {
                                x: { grid: { display: false }, ticks: { color: '#A9ABBD' } },
                                y: { grid: { color: '#1E2033' }, ticks: { color: '#A9ABBD' } }
                            }
                        }
                    });
                }

                const ordCtx = document.getElementById('ordersChart');
                if (ordCtx) {
                    new Chart(ordCtx, {
                        type: 'bar',
                        data: {
                            labels: ordLabels,
                            datasets: [{
                                label: 'Orders',
                                data: ordValues,
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => ` ${parseInt(ctx.raw, 10)}`
                                    }
                                }
                            },
                            scales: {
                                x: { grid: { display: false }, ticks: { color: '#A9ABBD' } },
                                y: { grid: { color: '#1E2033' }, ticks: { color: '#A9ABBD', precision: 0 } }
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
