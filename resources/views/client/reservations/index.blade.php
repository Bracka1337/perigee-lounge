@extends('layouts.client')

@section('client-header')
    <div class="mb-10">
        <p class="text-xs text-ink-muted mb-3">
            {{ __('client-reservations-badge') }}
        </p>
        <h1 class="logo-font text-3xl md:text-4xl mb-2">
            {{ __('client-reservations-title') }}
        </h1>
        <p class="text-sm md:text-base text-ink-muted">
            {{ __('client-reservations-subtitle') }}
        </p>
    </div>
@endsection

@section('client-content')
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
        // A whole-room booking counts for every table in its room.
        $reservationsByTable = app(\App\Services\TableHolds::class)->byTable($reservations);
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-2xl border border-line bg-surface p-4  overflow-hidden min-w-0">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-3 min-w-0">
                <h2 class="min-w-0 text-xs sm:text-xs text-ink-muted whitespace-normal break-words inline-flex items-center gap-2">
                    <x-icon category="Calendar" name="Calendar_Days" class="w-5 h-5 shrink-0"/>
                    {{ __('client-reservations-plan-title', ['date' => $date->format('d.m.Y')]) }}
                </h2>

                <form id="clientPlanDateForm" method="GET" class="min-w-0 w-full sm:w-auto flex flex-col sm:flex-row gap-2 text-xs">
                    <input type="date"
                           id="clientPlanDateInput"
                           name="date"
                           value="{{ $date->toDateString() }}"
                           class="block w-full min-w-0 max-w-full appearance-none bg-ground/60 border border-line rounded-xl px-3 py-1.5 text-xs text-ink focus:outline-none focus:ring-1 focus:ring-line-strong">
                </form>
            </div>

            <div class="flex flex-wrap items-start justify-between gap-x-3">
                <x-floor-plan.tabs :floors="$floors" :active="$activeFloorId" />

                {{-- 2D is the default. The 3D view is offered on large screens with WebGL (see lounge-toggle.js). --}}
                <div data-view-toggle class="mb-3 hidden rounded-full border border-line p-0.5" role="group" aria-label="{{ __('lounge-view-toggle-label') }}">
                    @foreach (['2d' => __('lounge-view-2d'), '3d' => __('lounge-view-3d')] as $view => $label)
                        <button type="button" data-view="{{ $view }}" aria-pressed="{{ $view === '2d' ? 'true' : 'false' }}"
                                class="min-h-11 min-w-12 rounded-full px-3 text-xs text-ink-muted transition hover:text-ink aria-pressed:bg-nebula aria-pressed:font-semibold aria-pressed:text-on-nebula">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div data-plan-3d class="hidden">
                <div data-lounge-viewer data-active-floor="{{ $activeFloorId }}" data-label-day="{{ __('lounge-3d-day') }}" data-label-evening="{{ __('lounge-3d-evening') }}"
                     class="relative aspect-[16/14] w-full overflow-hidden rounded-2xl border border-line bg-ground">
                    <p data-lounge-loading class="absolute inset-0 flex items-center justify-center text-xs text-ink-muted">{{ __('lounge-3d-loading') }}</p>
                </div>
                <p class="mt-2 text-xs text-ink-muted">{{ __('lounge-3d-hint') }}</p>
            </div>

            <div class="overflow-x-auto" data-plan-2d>
                @foreach ($floors as $floor)
                    <x-floor-plan.stage :floor="$floor" :frame="$frame" :active="$activeFloorId" :room-booking="$roomBooking" :room-renders="$roomRenders">

                        @foreach ($floor->tables as $table)
                    @php
                        $tableReservations = $reservationsByTable->get($table->id, collect());

                        $status = 'free';
                        if (isset($occupiedTableIds) && in_array($table->id, $occupiedTableIds)) {
                            $status = 'active';
                        } elseif ($tableReservations->contains(fn($r) => $r->status === \App\Enums\ReservationStatus::Active)) {
                            $status = 'active';
                        } elseif ($tableReservations->contains(fn($r) => $r->status === \App\Enums\ReservationStatus::Pending)) {
                            $status = 'pending';
                        }

                        $statusClass = match($status) {
                            'active'  => 'bg-emerald-500/80 border-emerald-300',
                            'pending' => 'bg-sky-500/80 border-sky-300',
                            default   => 'bg-line border-line-strong',
                        };

                        $disabled = $status !== 'free' || !$table->is_active;
                        $statusLabels = [
                            'free' => __('client-reservations-legend-free'),
                            'pending' => __('client-reservations-legend-pending'),
                            'active' => __('client-reservations-legend-active'),
                        ];
                    @endphp

                    <button type="button"
                            class="js-client-table absolute flex flex-col items-center justify-center rounded-xl border text-xs text-ink {{ $statusClass }}"
                            data-id="{{ $table->id }}"
                            data-name="{{ $table->name }}"
                            data-capacity="{{ $table->capacity }}"
                            data-disabled="{{ $disabled ? '1' : '0' }}"
                            data-status="{{ $status }}"
                            data-status-label="{{ $statusLabels[$status] }}"
                            data-room="{{ $table->room?->t('name') }}"
                            data-floor="{{ $floor->t('name') }}"
                            data-render="{{ $tableRenders[$table->id] ?? '' }}"
                            style="{{ $frame->tableStyle($table) }} opacity: {{ $table->is_active ? '1' : '0.3' }};">
                        <div class="text-ink font-semibold text-xs md:text-xs leading-tight max-w-full px-1 truncate text-center">
                            {{ $table->name }}
                        </div>
                    </button>
                @endforeach
                    </x-floor-plan.stage>
                @endforeach
            </div>

            <div class="mt-4 flex flex-col sm:flex-row sm:flex-wrap gap-2 sm:gap-3 text-xs text-ink-muted">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500/80"></span>
                    <span>{{ __('client-reservations-legend-active') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-sky-500/80"></span>
                    <span>{{ __('client-reservations-legend-pending') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-line"></span>
                    <span>{{ __('client-reservations-legend-free') }}</span>
                </div>
            </div>

            <x-floor-plan.legend :floors="$floors" :active="$activeFloorId" />
        </div>

        <div class="rounded-2xl border border-line bg-surface p-4  overflow-hidden min-w-0">
            <h2 class="text-xs text-ink-muted mb-3 inline-flex items-center gap-2">
                <x-icon category="Interface" name="Book" class="w-5 h-5"/>
                {{ __('client-reservations-sidebar-title') }}
            </h2>

            {{-- Details of the selected table, with its generated picture when there is one. --}}
            <div id="clientTablePanel" class="mb-4 hidden overflow-hidden rounded-2xl border border-line bg-ground/60" aria-live="polite">
                <figure data-panel-figure class="relative hidden">
                    <img data-panel-image alt="" width="600" height="400" class="aspect-[3/2] w-full object-cover">
                    <figcaption class="absolute left-2 top-2 rounded-full bg-ground/85 px-2.5 py-1 text-[11px] text-ink-soft">
                        {{ __('client-reservations-panel-render-caption') }}
                    </figcaption>
                </figure>
                <div class="space-y-2 p-3">
                    <p class="text-sm font-semibold text-ink" data-panel-name></p>
                    <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-xs">
                        @foreach (['room' => __('client-reservations-panel-room'), 'floor' => __('client-reservations-panel-floor'), 'capacity' => __('client-reservations-panel-capacity'), 'status' => __('client-reservations-panel-status')] as $field => $fieldLabel)
                            <dt class="text-ink-muted" data-panel-row="{{ $field }}">{{ $fieldLabel }}</dt>
                            <dd class="text-ink-soft" data-panel-row="{{ $field }}" data-panel-value="{{ $field }}"></dd>
                        @endforeach
                    </dl>
                </div>
            </div>

            <form id="clientReservationForm"
                  method="POST"
                  action="{{ route('client.reservations.store', ['locale' => app()->getLocale()]) }}"
                  class="space-y-3">
                @csrf
                <input type="hidden" name="table_id" id="client_table_id" value="{{ old('table_id') }}">
                <input type="hidden" name="room_id" id="client_room_id" value="{{ old('room_id') }}">

                <div class="text-xs text-ink-muted mb-1" id="clientSelectedTableLabel">
                    {{ __('client-reservations-selected-table-empty') }}
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs text-ink-muted">
                        {{ __('client-reservations-field-date') }}
                    </label>
                    <div class="relative flex items-center gap-2">
                        <input type="date"
                               id="clientDateInput"
                               name="date"
                               value="{{ old('date', $date->toDateString()) }}"
                               class="block w-full min-w-0 max-w-full appearance-none bg-ground/60 border border-line rounded-xl px-3 py-1.5 text-xs text-ink focus:outline-none focus:ring-1 focus:ring-line-strong">

                        <button type="button"
                                class="hidden md:inline-flex items-center justify-center shrink-0 w-8 h-8 rounded-xl bg-line border border-line text-ink hover:bg-line focus:outline-none focus:ring-1 focus:ring-line-strong"
                                aria-label="{{ __('client-reservations-field-date') }}"
                                onclick="openInputPicker('clientDateInput')"
                                title="{{ __('client-reservations-field-date') }}">
                            <x-icon category="Calendar" name="Calendar_Days" class="w-4 h-4"/>
                        </button>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs text-ink-muted">
                        {{ __('client-reservations-field-time') }}
                    </label>
                    <div class="relative flex items-center gap-2">
                        <!-- Mobile/native time picker -->
                        <input type="time"
                               id="clientTimeInput"
                               name="time"
                               value="{{ old('time', '19:00') }}"
                               class="block w-full min-w-0 max-w-full appearance-none bg-ground/60 border border-line rounded-xl px-3 py-1.5 text-xs text-ink focus:outline-none focus:ring-1 focus:ring-line-strong md:hidden">

                        <!-- Desktop segmented time (HH:MM) -->
                        <div class="hidden md:flex items-center gap-2 w-full">
                            <div class="flex items-center w-full min-w-0 appearance-none bg-ground/60 border border-line rounded-xl px-3 py-1.5 text-xs text-ink focus-within:outline-none focus-within:ring-1 focus-within:ring-line-strong">
                                <input type="text"
                                    id="clientTimeHours"
                                    inputmode="numeric"
                                    maxlength="2"
                                    placeholder="HH"
                                    class="w-10 bg-transparent text-ink text-xs text-center px-0 py-0 leading-none focus:outline-none"
                                    aria-label="Hours">
                                <span class="text-ink-muted select-none">:</span>
                                <input type="text"
                                    id="clientTimeMinutes"
                                    inputmode="numeric"
                                    maxlength="2"
                                    placeholder="MM"
                                    class="w-10 bg-transparent text-ink text-xs text-center px-0 py-0 leading-none focus:outline-none"
                                    aria-label="Minutes">
                            </div>

                            <button type="button"
                                    class="inline-flex items-center justify-center shrink-0 w-8 h-8 rounded-xl bg-line border border-line text-ink hover:bg-line focus:outline-none focus:ring-1 focus:ring-line-strong"
                                    aria-label="{{ __('client-reservations-field-time') }}"
                                    onclick="focusSegmentedTime()"
                                    title="{{ __('client-reservations-field-time') }}">
                                <x-icon category="Calendar" name="Clock" class="w-4 h-4"/>
                            </button>
                        </div>

                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs text-ink-muted">
                        {{ __('client-reservations-field-guests') }}
                    </label>
                    <input type="number"
                           name="guests_count"
                           value="{{ old('guests_count', 2) }}"
                           min="1"
                           max="20"
                           class="block w-full min-w-0 max-w-full appearance-none bg-ground/60 border border-line rounded-xl px-3 py-1.5 text-xs text-ink focus:outline-none focus:ring-1 focus:ring-line-strong">
                </div>

                <p class="text-xs text-ink-muted">
                    {{ __('client-reservations-rules') }}
                </p>

                <button type="submit"
                        class="w-full mt-2 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full border border-line-strong text-xs text-ink hover:bg-ink hover:text-on-nebula transition">
                    <x-icon category="Edit" name="Add_Plus_Circle" class="w-5 h-5"/>
                    {{ __('client-reservations-submit') }}
                </button>
            </form>

            <div class="mt-6">
                <h3 class="text-xs text-ink-muted mb-2 inline-flex items-center gap-2">
                    <x-icon category="Calendar" name="Calendar_Days" class="w-5 h-5"/>
                    {{ __('client-reservations-my-title') }}
                </h3>

                <div class="space-y-4 max-h-64 overflow-y-auto pr-1">
                    @forelse($myReservations as $res)
<div class="rounded-xl bg-ground/40 border border-line px-3 py-2 text-xs text-ink-soft">
    <div class="min-w-0">
        <div class="text-xs text-ink-muted whitespace-nowrap">
            {{ $res->start_at->format('d.m.Y H:i') }}
        </div>

        <div class="mt-0.5 text-ink whitespace-nowrap">
            {{ $res->room_id ? __('room-booking-label').' '.($res->room?->t('name') ?? '') : __('client-reservations-table-label').' '.($res->table->name ?? '#') }}
        </div>

        <div class="text-xs text-ink-muted whitespace-nowrap">
            {{ __('client-reservations-guests-label') }}: {{ $res->guests_count }}
        </div>

        <div class="mt-1 text-xs text-ink-muted leading-snug whitespace-normal">
            {{ __('client-reservations-status-label') }}:
            <span class="break-words">
                {{ __('client-reservations-status-'.$res->status->value) }}
            </span>
        </div>

        @if($res->status === \App\Enums\ReservationStatus::Pending)
            <form method="POST"
                  action="{{ route('client.reservations.cancel', ['locale' => app()->getLocale(), 'reservation' => $res]) }}"
                  class="mt-3 flex justify-center">
                @csrf

                <button type="submit"
                        class="inline-flex items-center gap-1.5
                               px-3 py-1.5 rounded-full
                               border border-red-400/35 bg-ground/30 text-red-200
                               hover:bg-red-500/15 hover:border-red-300/50
                               active:scale-[0.98] transition
                               whitespace-nowrap">
                    <x-icon category="Edit" name="Close_Circle" class="w-4 h-4"/>
                    <span class="text-xs leading-none">
                        {{ __('client-reservations-cancel-button') }}
                    </span>
                </button>
            </form>
        @endif
    </div>
</div>

                    @empty
                        <p class="text-xs text-ink-muted">
                            {{ __('client-reservations-my-empty') }}
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="application/json" id="loungeLayoutData">@json($lounge3d)</script>
    @vite('resources/js/lounge-toggle.js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const buttons = document.querySelectorAll('.js-client-table');
            const tableIdInput = document.getElementById('client_table_id');
            const label = document.getElementById('clientSelectedTableLabel');
            const dateInput = document.getElementById('clientPlanDateInput');
            const dateForm = document.getElementById('clientPlanDateForm');

            let selectedBtn = null;

            const panel = document.getElementById('clientTablePanel');
            const panelTitle = @json(__('client-reservations-panel-table', ['name' => '__NAME__']));

            function showTablePanel(btn, title = panelTitle) {
                if (!panel) return;

                const values = { room: btn.dataset.room, floor: btn.dataset.floor, capacity: btn.dataset.capacity, status: btn.dataset.statusLabel };
                panel.querySelector('[data-panel-name]').textContent = title.replace('__NAME__', () => btn.dataset.name);
                Object.entries(values).forEach(([field, value]) => {
                    panel.querySelectorAll(`[data-panel-row="${field}"]`).forEach((row) => row.classList.toggle('hidden', !value));
                    panel.querySelector(`[data-panel-value="${field}"]`).textContent = value ?? '';
                });

                const figure = panel.querySelector('[data-panel-figure]');
                const image = panel.querySelector('[data-panel-image]');
                figure.classList.toggle('hidden', !btn.dataset.render);
                if (btn.dataset.render) {
                    image.src = btn.dataset.render;
                    image.alt = title.replace('__NAME__', () => btn.dataset.name);
                }

                panel.classList.remove('hidden');
            }

            buttons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const disabled = btn.dataset.disabled === '1';
                    if (disabled) return;

                    if (selectedBtn && selectedBtn !== btn) {
                        selectedBtn.classList.remove('ring-2', 'ring-line-strong');
                    }

                    btn.classList.add('ring-2', 'ring-line-strong');
                    selectedBtn = btn;

                    const id = btn.dataset.id;
                    const name = btn.dataset.name;
                    const capacity = btn.dataset.capacity;

                    tableIdInput.value = id;
                    clearRoom();
                    showTablePanel(btn);
                    label.textContent = name + ' · ' + '{{ __('client-reservations-guests-label') }}' + ' ' + capacity;
                });
            });

            if (dateInput && dateForm) {
                dateInput.addEventListener('change', () => dateForm.submit());
            }

            // Whole-room booking: the name of a bookable room selects the room instead of a table.
            const roomIdInput = document.getElementById('client_room_id');
            const guestsInput = document.querySelector('[name="guests_count"]');
            const roomTitle = @json(__('room-booking-panel-title', ['name' => '__NAME__']));
            const roomLabel = @json(__('room-booking-selected', ['name' => '__NAME__', 'seats' => '__SEATS__']));
            const statusFree = @json(__('client-reservations-legend-free'));
            const statusTaken = @json(__('room-booking-taken'));
            const roomButtons = document.querySelectorAll('.js-room-book');
            let selectedRoom = null;

            function clearRoom() {
                roomIdInput.value = '';
                selectedRoom?.setAttribute('aria-pressed', 'false');
                selectedRoom = null;
                guestsInput?.setAttribute('max', '20');
            }

            function selectRoom(button) {
                if (selectedBtn) selectedBtn.classList.remove('ring-2', 'ring-line-strong');
                selectedBtn = null;
                tableIdInput.value = '';
                selectedRoom?.setAttribute('aria-pressed', 'false');
                selectedRoom = button;
                button.setAttribute('aria-pressed', 'true');
                roomIdInput.value = button.dataset.roomId;
                guestsInput?.setAttribute('max', button.dataset.seats);
                label.textContent = roomLabel.replace('__NAME__', () => button.dataset.name).replace('__SEATS__', button.dataset.seats);
                showTablePanel({ dataset: {
                    name: button.dataset.name, room: '', floor: button.dataset.floor, capacity: button.dataset.seats,
                    statusLabel: button.dataset.taken === '1' ? statusTaken : statusFree, render: button.dataset.render,
                } }, roomTitle);
            }

            roomButtons.forEach((button) => {
                button.disabled = button.dataset.taken === '1';
                if (button.disabled) button.title = statusTaken;
                button.addEventListener('click', () => selectRoom(button));
            });

            // After a failed submit, show the room again.
            if (roomIdInput.value) {
                const again = [...roomButtons].find((button) => button.dataset.roomId === roomIdInput.value);
                if (again && !again.disabled) selectRoom(again);
            }


            // Initialize segmented time from hidden/native input
            const timeInput = document.getElementById('clientTimeInput');
            const hhEl = document.getElementById('clientTimeHours');
            const mmEl = document.getElementById('clientTimeMinutes');

            function pad2(n) { n = parseInt(n ?? 0, 10); if (isNaN(n)) n = 0; return String(n).padStart(2, '0'); }
            function clamp(n, min, max) { n = parseInt(n ?? 0, 10); if (isNaN(n)) n = min; return Math.min(max, Math.max(min, n)); }

            function syncFromNative() {
                if (!timeInput || !hhEl || !mmEl) return;
                const val = (timeInput.value || '').trim();
                const parts = val.split(':');
                const hh = pad2(parts[0] ?? '19');
                const mm = pad2(parts[1] ?? '00');
                hhEl.value = hh;
                mmEl.value = mm;
            }

            function syncToNative() {
                if (!timeInput || !hhEl || !mmEl) return;
                const hh = clamp(hhEl.value, 0, 23);
                const mm = clamp(mmEl.value, 0, 59);
                timeInput.value = pad2(hh) + ':' + pad2(mm);
            }

            function setupSegmentedHandlers() {
                if (!hhEl || !mmEl) return;

                const filterDigits = (el) => {
                    el.value = (el.value || '').replace(/\D+/g, '').slice(0, 2);
                };

                hhEl.addEventListener('input', () => {
                    filterDigits(hhEl);
                    const v = hhEl.value;
                    if (v.length >= 2) {
                        // clamp 00-23 and pad
                        const clamped = clamp(v, 0, 23);
                        hhEl.value = pad2(clamped);
                        syncToNative();
                        mmEl.focus({ preventScroll: true });
                        try { mmEl.select(); } catch(e) { try { mmEl.setSelectionRange(0, (mmEl.value||'').length); } catch(e2) {} }
                    } else {
                        syncToNative();
                    }
                });

                hhEl.addEventListener('blur', () => {
                    filterDigits(hhEl);
                    if (hhEl.value.length > 0) {
                        const clamped = clamp(hhEl.value, 0, 23);
                        hhEl.value = pad2(clamped);
                        syncToNative();
                    }
                });

                mmEl.addEventListener('input', () => {
                    filterDigits(mmEl);
                    const v = mmEl.value;
                    if (v.length >= 2) {
                        const clamped = clamp(v, 0, 59);
                        mmEl.value = pad2(clamped);
                    }
                    syncToNative();
                });

                mmEl.addEventListener('blur', () => {
                    filterDigits(mmEl);
                    if (mmEl.value.length > 0) {
                        const clamped = clamp(mmEl.value, 0, 59);
                        mmEl.value = pad2(clamped);
                        syncToNative();
                    }
                });
            }

            syncFromNative();
            setupSegmentedHandlers();
        });

        function openInputPicker(id) {
            const el = document.getElementById(id);
            if (!el) return;
            // On desktop, prefer segmented inputs for precise control
            const hhEl = document.getElementById('clientTimeHours');
            const mmEl = document.getElementById('clientTimeMinutes');
            // Only apply segmented-time focus when clicking the time icon
            if (id === 'clientTimeInput' && hhEl && mmEl && window.matchMedia('(min-width: 768px)').matches) {
                hhEl.focus({ preventScroll: true });
                try { hhEl.select(); } catch(e) { try { hhEl.setSelectionRange(0, (hhEl.value||'').length); } catch(e2) {} }
                return;
            }

            // Focus first to increase success chance across browsers
            try { el.focus({ preventScroll: true }); } catch(e) { el.focus(); }

            const attempt = () => {
                if (typeof el.showPicker === 'function') {
                    try { el.showPicker(); return; } catch(e) {}
                }
                try { el.click(); } catch(e) {}
            };

            // Slight delay helps in Chrome/Safari after focusing
            setTimeout(() => {
                attempt();
                // Only select text for time input; date should just open picker
                if (id === 'clientTimeInput') {
                    try { el.select(); } catch(e) { try { el.setSelectionRange(0, (el.value||'').length); } catch(e2) {} }
                }
            }, 20);
        }

        function focusSegmentedTime() {
            const hhEl = document.getElementById('clientTimeHours');
            if (hhEl) {
                hhEl.focus({ preventScroll: true });
                try { hhEl.select(); } catch(e) { try { hhEl.setSelectionRange(0, (hhEl.value||'').length); } catch(e2) {} }
            }
        }
    </script>
@endpush
