@extends('layouts.staff')

@section('content')
@php
    $locale = app()->getLocale();
@endphp

<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div class="min-w-0">
        <h1 class="logo-font text-3xl md:text-4xl mb-2">
            {{ __('staff-orders-title') }}
        </h1>
        <p class="text-sm text-ink-muted">
            {{ __('staff-orders-subtitle') }}
        </p>
    </div>

    {{-- DATE --}}
    <div class="shrink-0 w-full md:w-auto">
        <form id="staffOrdersDateForm" method="GET" action="{{ url()->current() }}" class="w-full md:w-auto">
            @foreach(request()->except(['date','page']) as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach

            <div class="w-full md:w-[190px]">
                <div class="inline-flex w-full items-center gap-2 rounded-xl border border-line bg-ground/60 px-3 py-2">
                    <input
                        type="date"
                        id="staffOrdersDateInput"
                        name="date"
                        value="{{ $date->toDateString() }}"
                        class="w-full bg-transparent border-0 p-0 text-xs text-ink focus:outline-none focus:ring-0"
                    >

                    <button
                        type="button"
                        class="shrink-0 text-ink-muted hover:text-ink transition cursor-pointer"
                        aria-label="Open calendar"
                        onclick="(function(){var el=document.getElementById('staffOrdersDateInput');if(!el)return; if(typeof el.showPicker==='function'){el.showPicker();}else{el.focus();try{el.dispatchEvent(new MouseEvent('mousedown',{view:window,bubbles:true,cancelable:true}))}catch(e){}}})()"
                    >
                        <x-icon category="Calendar" name="Calendar_Days" class="w-4 h-4"/>
                    </button>
                </div>
            </div>
        </form>
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

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- ACTIVE --}}
    <div class="rounded-2xl border border-line bg-surface p-4 md:p-5 overflow-hidden min-w-0">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <h2 class="text-xs sm:text-xs text-ink-muted inline-flex items-center gap-2 min-w-0">
                <x-icon category="Calendar" name="Calendar_Days" class="w-5 h-5 shrink-0"/>
                <span class="truncate">{{ __('staff-orders-active-title', ['date' => $date->format('d.m.Y')]) }}</span>
            </h2>

            <a href="{{ route('staff.orders.create', ['locale' => $locale]) }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full border border-line-strong
                      text-xs text-ink hover:bg-ink hover:text-on-nebula transition whitespace-nowrap">
                <x-icon category="Edit" name="Add_Plus_Circle" class="w-5 h-5"/>
                <span>{{ __('staff-orders-create-button') }}</span>
            </a>
        </div>

        <div class="space-y-3 max-h-[420px] overflow-y-auto pr-1">
            @forelse($ordersActive as $order)
                @php
                    $status = $order->status;
                    $statusClass = match ($status) {
                        \App\Enums\OrderStatus::Open => 'text-emerald-200 bg-emerald-500/15 border-emerald-400/60',
                        \App\Enums\OrderStatus::Completed => 'text-slate-200 bg-slate-500/20 border-slate-400/50',
                        \App\Enums\OrderStatus::Cancelled => 'text-red-200 bg-red-500/15 border-red-400/50',
                    };
                @endphp

                <div class="rounded-2xl bg-ground/40 border border-line px-4 py-3 text-xs text-ink-soft min-w-0">
                    {{-- ВАЖНО: на мобиле всё столбиком, без наезда --}}
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs text-ink-muted">
                                #{{ $order->id }} · {{ $order->created_at->format('d.m H:i') }}
                            </div>

                            {{-- ВАЖНО: ТОЛЬКО СТРОКИ (как Enter) --}}
                            <div class="mt-2 space-y-1 min-w-0">
                                <div class="flex items-center gap-2 text-ink-soft min-w-0">
                                    <x-icon category="System" name="Window" class="w-4 h-4 shrink-0"/>
                                    <span class="truncate">
                                        {{ __('staff-orders-table-label') }} {{ $order->table->name ?? '#' }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 text-ink-muted min-w-0">
                                    <x-icon category="User" name="User_01" class="w-4 h-4 shrink-0"/>
                                    <span class="truncate">
                                        {{ $order->user?->name ?? __('staff-orders-guest-label') }}
                                    </span>
                                </div>

                                <div>
                                    <span class="inline-flex px-2 py-0.5 rounded-full border {{ $statusClass }} text-xs">
                                        {{ __('staff-orders-status-'.$order->status->value) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- На мобиле цена+кнопка в одной строке, чтобы не ехало --}}
                        <div class="w-full sm:w-auto flex items-center justify-between sm:flex-col sm:items-end gap-2">
                            <div class="text-sm text-ink-soft font-semibold tabular-nums">
                                {{ number_format($order->total, 2, '.', ' ') }}&nbsp;€
                            </div>

                            <a href="{{ route('staff.orders.show', ['locale' => $locale, 'order' => $order]) }}"
                               class="h-10 w-10 inline-flex items-center justify-center rounded-full
                                      border border-line-strong bg-surface
                                      text-ink hover:bg-ink hover:text-on-nebula transition"
                               aria-label="{{ __('staff-orders-open-button') }}">
                                <x-icon category="Interface" name="External_Link" class="w-5 h-5"/>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-xs text-ink-muted">
                    {{ __('staff-orders-empty') }}
                </p>
            @endforelse
        </div>
    </div>

    {{-- RESERVATIONS --}}
    <div class="rounded-2xl border border-line bg-surface p-4 md:p-5 overflow-hidden min-w-0">
        <h2 class="text-xs sm:text-xs text-ink-muted mb-4 inline-flex items-center gap-2">
            <x-icon category="Calendar" name="Calendar_Days" class="w-5 h-5"/>
            {{ __('staff-orders-reservations-title') }}
        </h2>

        <div class="space-y-3 max-h-[420px] overflow-y-auto pr-1">
            @forelse($reservations as $res)
                <div class="rounded-2xl bg-ground/40 border border-line px-4 py-3 text-xs text-ink-soft min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs text-ink-muted">
                                {{ $res->start_at->format('d.m H:i') }}
                            </div>

                            <div class="mt-2 space-y-1 min-w-0">
                                <div class="flex items-center gap-2 text-ink-soft min-w-0">
                                    <x-icon category="System" name="Window" class="w-4 h-4 shrink-0"/>
                                    <span class="truncate">
                                        {{ __('staff-orders-table-label') }} {{ $res->table->name ?? '#' }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 text-ink-muted min-w-0">
                                    <x-icon category="User" name="User_01" class="w-4 h-4 shrink-0"/>
                                    <span class="truncate">
                                        {{ $res->guest_name ?? ($res->user->name ?? __('staff-orders-guest-label')) }}
                                    </span>
                                </div>

                                <div class="text-xs text-ink-muted">
                                    {{ __('staff-orders-status-label') }}:
                                    {{ __('staff-reservations-status-'.$res->status->value) }}
                                </div>
                            </div>
                        </div>

                        <div class="w-full sm:w-auto flex items-center justify-end">
                            <form method="POST"
                                  action="{{ route('staff.orders.from-reservation', ['locale' => $locale, 'reservation' => $res]) }}">
                                @csrf
                                <button type="submit"
                                        class="h-10 w-10 inline-flex items-center justify-center rounded-full
                                               border border-emerald-500/60 bg-emerald-500/10
                                               text-emerald-200 hover:bg-emerald-500/20 transition"
                                        aria-label="{{ __('staff-orders-start-from-reservation-button') }}">
                                    <x-icon category="Interface" name="Check_Big" class="w-5 h-5"/>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-xs text-ink-muted">
                    {{ __('staff-orders-reservations-empty') }}
                </p>
            @endforelse
        </div>
    </div>

    {{-- HISTORY --}}
    <div class="rounded-2xl border border-line bg-surface p-4 md:p-5 overflow-hidden min-w-0">
        <h2 class="text-xs sm:text-xs text-ink-muted mb-4 inline-flex items-center gap-2">
            <x-icon category="Interface" name="Book" class="w-5 h-5"/>
            {{ __('staff-orders-history-title', ['date' => $date->format('d.m.Y')]) }}
        </h2>

        <div class="space-y-3 max-h-[420px] overflow-y-auto pr-1">
            @forelse($ordersHistory as $order)
                @php
                    $status = $order->status;
                    $statusClass = match ($status) {
                        \App\Enums\OrderStatus::Open => 'text-emerald-200 bg-emerald-500/15 border-emerald-400/60',
                        \App\Enums\OrderStatus::Completed => 'text-slate-200 bg-slate-500/20 border-slate-400/50',
                        \App\Enums\OrderStatus::Cancelled => 'text-red-200 bg-red-500/15 border-red-400/50',
                    };
                @endphp

                <div class="rounded-2xl bg-ground/30 border border-line px-4 py-3 text-xs text-ink-soft min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs text-ink-muted">
                                #{{ $order->id }} · {{ $order->created_at->format('d.m H:i') }}
                            </div>

                            <div class="mt-2 space-y-1 min-w-0">
                                <div class="flex items-center gap-2 text-ink-soft min-w-0">
                                    <x-icon category="System" name="Window" class="w-4 h-4 shrink-0"/>
                                    <span class="truncate">
                                        {{ __('staff-orders-table-label') }} {{ $order->table->name ?? '#' }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 text-ink-muted min-w-0">
                                    <x-icon category="User" name="User_01" class="w-4 h-4 shrink-0"/>
                                    <span class="truncate">
                                        {{ $order->user?->name ?? __('staff-orders-guest-label') }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex px-2 py-0.5 rounded-full border {{ $statusClass }} text-xs">
                                        {{ __('staff-orders-status-'.$order->status->value) }}
                                    </span>

                                    @if($order->completed_at)
                                        <span class="text-xs text-ink-muted">
                                            {{ $order->completed_at->format('H:i') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="w-full sm:w-auto flex items-center justify-between sm:flex-col sm:items-end gap-2">
                            <div class="text-sm text-ink-soft font-semibold tabular-nums">
                                {{ number_format($order->total, 2, '.', ' ') }}&nbsp;€
                            </div>

                            <a href="{{ route('staff.orders.show', ['locale' => $locale, 'order' => $order]) }}"
                               class="h-10 w-10 inline-flex items-center justify-center rounded-full
                                      border border-line-strong bg-surface
                                      text-ink hover:bg-ink hover:text-on-nebula transition"
                               aria-label="{{ __('staff-orders-open-button') }}">
                                <x-icon category="Interface" name="External_Link" class="w-5 h-5"/>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-xs text-ink-muted">
                    {{ __('staff-orders-history-empty') }}
                </p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('staffOrdersDateInput');
  const form  = document.getElementById('staffOrdersDateForm');
  if (!input || !form) return;

  input.addEventListener('change', () => form.submit());
});
</script>
@endpush
