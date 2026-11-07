@extends('layouts.staff')

@section('content')
@php
    $locale = app()->getLocale();
@endphp

<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div class="min-w-0">
        <h1 class="logo-font text-3xl md:text-4xl mb-2">
            <span class="truncate">{{ __('staff-reservations-title') }}</span>
        </h1>
        <p class="text-sm text-ink-muted">
            {{ __('staff-reservations-subtitle') }}
        </p>
    </div>

    {{-- DATE (сверху) --}}
    <div class="shrink-0 w-full md:w-auto">
        <form id="staffReservationsDateForm" method="GET" action="{{ url()->current() }}" class="w-full md:w-auto">
            @foreach(request()->except(['date','page']) as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach

            <div class="w-full md:w-[190px] min-w-0">
                <div class="inline-flex w-full items-center gap-2 rounded-xl border border-line-strong bg-ground/60 px-3 py-2 overflow-hidden">
                    <input
                        id="staffReservationsDateInput"
                        type="date"
                        name="date"
                        value="{{ $date->toDateString() }}"
                        class="w-full min-w-0 max-w-full bg-transparent border-0 p-0 text-xs text-ink focus:outline-none focus:ring-0 appearance-none"
                        style="color-scheme: dark;"
                    >

                    <label for="staffReservationsDateInput" class="shrink-0 text-ink-muted cursor-pointer hover:text-ink transition">
                        <x-icon category="Calendar" name="Calendar_Days" class="w-4 h-4"/>
                    </label>
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

@if($errors->any())
    <div class="mb-4 text-xs text-red-300 border border-red-500/60 bg-red-500/10 px-4 py-2 rounded-lg">
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $reservationsByTable = $reservations->groupBy('table_id');
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- PLAN --}}
    <div class="lg:col-span-2 rounded-2xl border border-line bg-surface p-4 overflow-hidden min-w-0">
        <div class="flex items-center justify-between mb-3 gap-3 min-w-0">
            <h2 class="text-xs text-ink-muted inline-flex items-center gap-2 min-w-0">
                <x-icon category="System" name="Window" class="w-5 h-5 shrink-0"/>
                <span class="truncate">
                    {{ __('staff-reservations-plan-title', ['date' => $date->format('d.m.Y')]) }}
                </span>
            </h2>
        </div>

        <div class="overflow-x-auto">
<div id="staffFloorPlan"
             class="relative w-full min-w-[640px] aspect-[16/14] rounded-2xl border border-line bg-ground/60 overflow-hidden select-none">
            <!-- Floor Plan Zones -->
            <x-floor-plan-zones />
            
            @foreach($tables as $table)
                @php
                    $tableReservations = $reservationsByTable->get($table->id, collect());

                    $status = 'free';

                    if (isset($occupiedTableIds) && in_array($table->id, $occupiedTableIds)) {
                        $status = 'active';
                    } elseif ($date->isSameDay(\Carbon\Carbon::now()) && ($table->is_occupied ?? false)) {
                        $status = 'active';
                    } elseif ($tableReservations->contains(fn($r) => $r->status === \App\Enums\ReservationStatus::Active)) {
                        $status = 'active';
                    } elseif ($tableReservations->contains(fn($r) => $r->status === \App\Enums\ReservationStatus::Pending)) {
                        $status = 'pending';
                    }

                    $statusClass = match($status) {
                        'active'  => 'bg-emerald-500/70 border-emerald-300',
                        'pending' => 'bg-sky-500/70 border-sky-300',
                        default   => 'bg-line border-line-strong',
                    };

                    $disabled = (isset($occupiedTableIds) && in_array($table->id, $occupiedTableIds))
                                || $status !== 'free'
                                || !($table->is_active ?? true);
                @endphp

                <button type="button"
                        class="js-staff-table absolute flex items-center justify-center rounded-xl border text-xs text-ink {{ $statusClass }}
                               focus:outline-none transition
                               {{ $disabled ? 'opacity-60 cursor-not-allowed' : 'hover:brightness-110' }}"
                        data-id="{{ $table->id }}"
                        data-name="{{ $table->name }}"
                        data-capacity="{{ $table->capacity }}"
                        data-disabled="{{ $disabled ? '1' : '0' }}"
                        data-base-width="{{ $table->width }}"
                        data-base-height="{{ $table->height }}"
                        style="
                            left: {{ $table->pos_x }}%;
                            top: {{ $table->pos_y }}%;
                            width: {{ $table->width }}px;
                            height: {{ $table->height }}px;
                            transform: translate(-50%, -50%) rotate({{ $table->rotation }}deg);
                            opacity: {{ ($table->is_active ?? true) ? '1' : '0.3' }};
                        ">
                    <div class="text-ink font-semibold text-xs md:text-xs leading-tight max-w-full px-1 truncate text-center">
                        {{ $table->name }}
                    </div>
                </button>
            @endforeach
        </div>
</div>

        <div class="mt-4 flex flex-wrap gap-3 text-xs text-ink-muted">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500/80"></span>
                <span>{{ __('staff-reservations-legend-active') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-sky-500/80"></span>
                <span>{{ __('staff-reservations-legend-pending') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-line"></span>
                <span>{{ __('staff-reservations-legend-free') }}</span>
            </div>
        </div>

        <div class="mt-3 pt-3 border-t border-line flex flex-wrap gap-3 text-xs text-ink-muted">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded bg-nebula/15 border border-nebula"></span>
                <span>{{ __('floor-plan-zone-terrace') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded bg-blue-500/30 border border-blue-400/40"></span>
                <span>{{ __('floor-plan-zone-hall1') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded bg-green-500/30 border border-green-400/40"></span>
                <span>{{ __('floor-plan-zone-hall2') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded bg-pink-500/30 border border-pink-400/40"></span>
                <span>{{ __('floor-plan-zone-playstation') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded bg-orange-500/30 border border-orange-400/40"></span>
                <span>{{ __('floor-plan-zone-vip') }}</span>
            </div>
        </div>
    </div>

    {{-- FORM + LIST --}}
    <div class="rounded-2xl border border-line bg-surface p-4 overflow-hidden min-w-0">
        <h2 class="text-xs text-ink-muted mb-3 inline-flex items-center gap-2">
            <x-icon category="Interface" name="Book" class="w-5 h-5 shrink-0"/>
            {{ __('staff-reservations-sidebar-title') }}
        </h2>

        <form id="staffReservationForm"
              method="POST"
              action="{{ route('staff.reservations.store', ['locale' => app()->getLocale()]) }}"
              class="space-y-3">
            @csrf
            <input type="hidden" name="table_id" id="staff_table_id">
            <input type="hidden" name="mode" id="staff_mode" value="phone">

            <div class="rounded-2xl bg-ground/40 border border-line px-3 py-2 text-xs text-ink-soft min-w-0">
                <div class="inline-flex items-center gap-2 min-w-0">
                    <x-icon category="System" name="Window" class="w-5 h-5 shrink-0"/>
                    <span id="staffSelectedTableLabel" class="truncate">
                        {{ __('staff-reservations-selected-table-empty') }}
                    </span>
                </div>
            </div>

            <div class="flex gap-2 text-xs mb-2">
                <button type="button"
                        data-mode="phone"
                        class="js-mode-btn flex-1 px-3 py-1.5 rounded-full border border-line-strong text-ink bg-line inline-flex items-center justify-center gap-2">
                    <x-icon category="Interface" name="Phone" class="w-4 h-4"/>
                    {{ __('staff-reservations-mode-phone') }}
                </button>
            </div>

            {{-- DATE (внизу) — как сверху --}}
            <div class="space-y-1.5 min-w-0">
                <label class="block text-xs text-ink-muted">
                    {{ __('staff-reservations-field-date') }}
                </label>

                <div class="inline-flex w-full items-center gap-2 rounded-xl border border-line bg-ground/60 px-3 py-2 overflow-hidden">
                    <input
                        id="staffReservationDateInput"
                        type="date"
                        name="date"
                        value="{{ old('date', $date->toDateString()) }}"
                        class="w-full min-w-0 max-w-full bg-transparent border-0 p-0 text-xs text-ink focus:outline-none focus:ring-0 appearance-none"
                        style="color-scheme: dark;"
                    >

                    <label for="staffReservationDateInput" class="shrink-0 text-ink-muted cursor-pointer hover:text-ink transition">
                        <x-icon category="Calendar" name="Calendar_Days" class="w-4 h-4"/>
                    </label>
                </div>
            </div>

            {{-- TIME (внизу) — так же --}}
            <div class="space-y-1.5 js-time-field min-w-0">
                <label class="block text-xs text-ink-muted">
                    {{ __('staff-reservations-field-time') }}
                </label>

                {{-- Мобильная версия (type="time") --}}
                <div class="md:hidden inline-flex w-full items-center gap-2 rounded-xl border border-line bg-ground/60 px-3 py-2 overflow-hidden">
                    <input
                        type="time"
                        name="time"
                        value="{{ old('time', '19:00') }}"
                        class="w-full min-w-0 max-w-full bg-transparent border-0 p-0 text-xs text-ink focus:outline-none focus:ring-0 appearance-none"
                        style="color-scheme: dark;"
                    >

                    <label class="shrink-0 text-ink-muted cursor-pointer hover:text-ink transition">
                        <x-icon category="Time" name="Clock" class="w-4 h-4"/>
                    </label>
                </div>

                <div class="hidden md:block">
                    <div class="inline-flex w-full items-center gap-2 rounded-xl
                                border border-line bg-ground/60 px-3 py-1.5 overflow-hidden">
                        <input
                            id="staffReservationTimeHour"
                            type="text"
                            inputmode="numeric"
                            maxlength="2"
                            value="{{ old('time') ? explode(':', old('time'))[0] : '19' }}"
                            placeholder="{{ __('common-hours-placeholder') }}"
                            class="w-8 bg-transparent border-0 p-0
                                text-xs text-ink text-center leading-none
                                focus:outline-none focus:ring-0 appearance-none"
                        >

                        <span class="text-ink-muted leading-none">:</span>

                        <input
                            id="staffReservationTimeMinute"
                            type="text"
                            inputmode="numeric"
                            maxlength="2"
                            value="{{ old('time') ? explode(':', old('time'))[1] : '00' }}"
                            placeholder="{{ __('common-minutes-placeholder') }}"
                            class="w-8 bg-transparent border-0 p-0
                                text-xs text-ink text-center leading-none
                                focus:outline-none focus:ring-0 appearance-none"
                        >

                        <input type="hidden" name="time" id="staffReservationTimeInput"
                            value="{{ old('time', '19:00') }}">

                        <label class="shrink-0 ml-auto text-ink-muted cursor-pointer
                                    hover:text-ink transition leading-none"
                            onclick="document.getElementById('staffReservationTimeHour').focus()">
                            <x-icon category="Time" name="Clock" class="w-4 h-4"/>
                        </label>
                    </div>
                </div>

            </div>

            <div class="space-y-1.5">
                <label class="block text-xs text-ink-muted">
                    {{ __('staff-reservations-field-guests') }}
                </label>
                <input type="number"
                       name="guests_count"
                       value="{{ old('guests_count', 2) }}"
                       min="1"
                       max="20"
                       class="w-full bg-ground/60 border border-line rounded-xl px-3 py-1.5 text-xs text-ink focus:outline-none focus:ring-1 focus:ring-line-strong">
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs text-ink-muted">
                    {{ __('staff-reservations-field-guest-name') }}
                </label>
                <input type="text"
                       name="guest_name"
                       value="{{ old('guest_name') }}"
                       class="w-full bg-ground/60 border border-line rounded-xl px-3 py-1.5 text-xs text-ink focus:outline-none focus:ring-1 focus:ring-line-strong">
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs text-ink-muted">
                    {{ __('staff-reservations-field-guest-phone') }}
                </label>
                <input type="text"
                       name="guest_phone"
                       value="{{ old('guest_phone') }}"
                       class="w-full bg-ground/60 border border-line rounded-xl px-3 py-1.5 text-xs text-ink focus:outline-none focus:ring-1 focus:ring-line-strong">
            </div>

            <button type="submit"
                    class="w-full mt-2 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full border border-line-strong
                           text-xs text-ink hover:bg-ink hover:text-on-nebula transition">
                <x-icon category="Edit" name="Add_Plus_Circle" class="w-5 h-5"/>
                {{ __('staff-reservations-submit') }}
            </button>
        </form>

        {{-- LIST --}}
        <div class="mt-6">
            <h3 class="text-xs text-ink-muted mb-2 inline-flex items-center gap-2">
                <x-icon category="Calendar" name="Calendar_Days" class="w-5 h-5"/>
                {{ __('staff-reservations-list-title') }}
            </h3>

            <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                @forelse($reservations->sortBy('start_at') as $res)
                    <div class="rounded-xl bg-ground/40 border border-line px-3 py-2 text-xs text-ink-soft">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="text-xs text-ink-muted">
                                    {{ $res->start_at->format('d.m H:i') }}
                                    @if($res->end_at) – {{ $res->end_at->format('H:i') }} @endif
                                </div>
                                <div class="text-ink truncate">
                                    {{ __('staff-reservations-table-label') }} {{ $res->table->name ?? '#' }}
                                </div>
                                <div class="text-xs text-ink-muted truncate">
                                    {{ $res->guest_name ?? ($res->user->name ?? __('common-guest')) }}
                                </div>
                                <div class="mt-1 text-xs text-ink-muted">
                                    {{ __('staff-reservations-status-label') }}:
                                    {{ __('staff-reservations-status-'.$res->status->value) }}
                                </div>
                            </div>

                            <div class="flex flex-col items-end gap-2 shrink-0">
                                @if($res->status === \App\Enums\ReservationStatus::Pending)
                                    <form method="POST" action="{{ route('staff.reservations.arrive', ['locale' => app()->getLocale(), 'reservation' => $res]) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-emerald-500/70 text-xs text-emerald-300 hover:bg-emerald-500/20 hover:text-emerald-100 transition">
                                            <x-icon category="Interface" name="Check_Big" class="w-4 h-4"/>
                                            {{ __('staff-reservations-arrive-button') }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('staff.reservations.cancel', ['locale' => app()->getLocale(), 'reservation' => $res]) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-red-500/70 text-xs text-red-300 hover:bg-red-500/20 hover:text-red-100 transition">
                                            <x-icon category="Edit" name="Close_Circle" class="w-4 h-4"/>
                                            {{ __('staff-reservations-cancel-button') }}
                                        </button>
                                    </form>
                                @elseif($res->status === \App\Enums\ReservationStatus::Active)
                                    <form method="POST" action="{{ route('staff.reservations.complete', ['locale' => app()->getLocale(), 'reservation' => $res]) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-emerald-500/70 text-xs text-emerald-300 hover:bg-emerald-500/20 hover:text-emerald-100 transition">
                                            <x-icon category="Interface" name="Checkbox_Check" class="w-4 h-4"/>
                                            {{ __('staff-reservations-complete-button') }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('staff.reservations.cancel', ['locale' => app()->getLocale(), 'reservation' => $res]) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-red-500/70 text-xs text-red-300 hover:bg-red-500/20 hover:text-red-100 transition">
                                            <x-icon category="Edit" name="Close_Circle" class="w-4 h-4"/>
                                            {{ __('staff-reservations-cancel-button') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-ink-muted">{{ __('staff-reservations-empty') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dateForm  = document.getElementById('staffReservationsDateForm');
    const dateInput = document.getElementById('staffReservationsDateInput');
    if (dateInput && dateForm) dateInput.addEventListener('change', () => dateForm.submit());

    const buttons = document.querySelectorAll('.js-staff-table');
    const label = document.getElementById('staffSelectedTableLabel');
    const tableIdInput = document.getElementById('staff_table_id');

    let selectedBtn = null;

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.dataset.disabled === '1') return;

            if (selectedBtn && selectedBtn !== btn) selectedBtn.classList.remove('ring-2', 'ring-line-strong');

            btn.classList.add('ring-2', 'ring-line-strong');
            selectedBtn = btn;

            if (tableIdInput) tableIdInput.value = btn.dataset.id;
            if (label) label.textContent =
                btn.dataset.name + ' · ' + @json(__('staff-reservations-guests-label')) + ' ' + btn.dataset.capacity;
        });
    });

    function setupResponsiveFloorPlan(planId, tableSelector) {
        const plan = document.getElementById(planId);
        if (!plan) return;

        const tables = plan.querySelectorAll(tableSelector);
        if (!tables.length) return;

        const designWidth = 1200;

        const applyScale = () => {
            const planWidth = plan.clientWidth || 1;

            let scale = Math.min(1, planWidth / designWidth);
            if (scale < 0.3) scale = 0.3;

            tables.forEach(el => {
                const baseW = parseFloat(el.dataset.baseWidth || 0);
                const baseH = parseFloat(el.dataset.baseHeight || 0);
                if (baseW > 0 && baseH > 0) {
                    el.style.width = (baseW * scale) + 'px';
                    el.style.height = (baseH * scale) + 'px';
                }
            });
        };

        applyScale();

        if (window.ResizeObserver) {
            const ro = new ResizeObserver(applyScale);
            ro.observe(plan);
        } else {
            window.addEventListener('resize', applyScale);
        }
    }

    setupResponsiveFloorPlan('staffFloorPlan', '.js-staff-table');

    // Auto-focus minutes after selecting hours in time input
    const timeHourInput = document.getElementById('staffReservationTimeHour');
    const timeMinuteInput = document.getElementById('staffReservationTimeMinute');
    const timeHiddenInput = document.getElementById('staffReservationTimeInput');

    function updateHiddenTime() {
        const hour = timeHourInput.value.padStart(2, '0');
        const minute = timeMinuteInput.value.padStart(2, '0');
        timeHiddenInput.value = `${hour}:${minute}`;
    }

    if (timeHourInput && timeMinuteInput) {
        // Auto-focus to minutes after typing hour
        timeHourInput.addEventListener('input', function() {
            if (this.value.length >= 2 || parseInt(this.value) > 2) {
                timeMinuteInput.focus();
                timeMinuteInput.select();
            }
            updateHiddenTime();
        });

        // Auto-focus to minutes on arrow down/up in hour field
        timeHourInput.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter' || e.key === 'Tab') {
                setTimeout(() => {
                    timeMinuteInput.focus();
                    timeMinuteInput.select();
                }, 50);
            }
        });

        timeMinuteInput.addEventListener('input', updateHiddenTime);
        
        // Prevent invalid values
        timeHourInput.addEventListener('blur', function() {
            let val = parseInt(this.value) || 0;
            if (val > 23) val = 23;
            if (val < 0) val = 0;
            this.value = val;
            updateHiddenTime();
        });

        timeMinuteInput.addEventListener('blur', function() {
            let val = parseInt(this.value) || 0;
            if (val > 59) val = 59;
            if (val < 0) val = 0;
            this.value = val;
            updateHiddenTime();
        });
    }
});
</script>
@endpush
