@extends('layouts.staff')

@php
    $locale = app()->getLocale();

    // ВАЖНО: чтобы не падало, если контроллер не передал переменную
    $reservedTableIds = $reservedTableIds ?? [];
    $openOrderTableIds = $openOrderTableIds ?? [];

    $toIntCollection = function ($value) {
        if ($value instanceof \Illuminate\Support\Collection) {
            return $value->map(fn($v) => (int)$v)->values();
        }
        if (is_array($value)) {
            return array_is_list($value)
                ? collect($value)->map(fn($v) => (int)$v)->values()
                : collect(array_keys($value))->map(fn($v) => (int)$v)->values();
        }
        return collect();
    };

    $reservedIds = $toIntCollection($reservedTableIds);
    $openOrderIds = $toIntCollection($openOrderTableIds);
@endphp

@section('content')
    <div class="mb-8">
        <h1 class="logo-font text-3xl md:text-4xl mb-2 drop-shadow-[0_0_10px_rgba(255,255,255,0.7)]">
            {{ __('staff-orders-create-title') }}
        </h1>
        <p class="text-sm text-white/70">
            {{ __('staff-orders-create-subtitle') }}
        </p>
    </div>

    @if($errors->any())
        <div class="mb-4 text-xs text-red-300 border border-red-500/60 bg-red-500/10 px-4 py-2 rounded-lg">
            {{ __('staff-orders-create-error-validation') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 text-xs text-red-300 border border-red-500/60 bg-red-500/10 px-4 py-2 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-3xl border border-white/10 bg-white/5 p-4 shadow-xl shadow-black/60 overflow-hidden min-w-0">
            <div class="flex items-center justify-between gap-3 mb-3 min-w-0">
                <h2 class="min-w-0 text-[10px] sm:text-xs uppercase tracking-[0.22em] text-white/60 inline-flex items-center gap-2">
                    <x-icon category="System" name="Window" class="w-5 h-5 shrink-0"/>
                    {{ __('staff-orders-field-table') }}
                </h2>

                <div class="text-[10px] uppercase tracking-[0.18em] text-white/50">
                    {{ __('staff-orders-field-table-placeholder') }}
                </div>
            </div>

            <div id="staffFloorPlan"
                 class="relative w-full aspect-[16/14] rounded-2xl border border-white/15 bg-black/70 overflow-hidden select-none">
                <!-- Floor Plan Zones -->
                <x-floor-plan-zones />
                
                @foreach($tables as $table)
                    @php
                        $isActive = (bool)($table->is_active ?? true);

                        $hasReservation = $reservedIds->contains((int)$table->id);
                        $hasOpenOrder   = $openOrderIds->contains((int)$table->id);

                        $statusClass = match (true) {
                            $hasOpenOrder   => 'bg-emerald-500/70 border-emerald-300', // гость за столом (как в бронированиях)
                            $hasReservation => 'bg-sky-500/70 border-sky-300',         // бронь (как в бронированиях)
                            default         => 'bg-white/10 border-white/60',
                        };

                        $disabled = !$isActive || $hasOpenOrder || $hasReservation;
                    @endphp

                    <button type="button"
                            class="js-staff-table absolute flex items-center justify-center rounded-xl border text-[11px] text-white {{ $statusClass }}
                                   transition focus:outline-none
                                   {{ $disabled ? 'cursor-not-allowed opacity-60' : 'hover:brightness-110' }}"
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
                                opacity: {{ $isActive ? '1' : '0.3' }};
                            ">
                        <div class="text-white font-semibold text-[10px] md:text-[11px] leading-tight max-w-full px-1 truncate text-center">
                            {{ $table->name }}
                        </div>
                    </button>
                @endforeach
            </div>

            <div class="mt-4 flex flex-wrap gap-3 text-[10px] uppercase tracking-[0.18em] text-white/60">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500/70"></span>
                    <span>{{ __('staff-reservations-legend-active') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-sky-500/70"></span>
                    <span>{{ __('staff-reservations-legend-pending') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-white/30"></span>
                    <span>{{ __('staff-reservations-legend-free') }}</span>
                </div>
            </div>

            <div class="mt-3 pt-3 border-t border-white/10 flex flex-wrap gap-3 text-[10px] uppercase tracking-[0.18em] text-white/50">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded bg-purple-500/30 border border-purple-400/40"></span>
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

        <div class="rounded-3xl border border-white/10 bg-white/5 p-4 shadow-xl shadow-black/60 overflow-hidden min-w-0">
            <h2 class="text-xs uppercase tracking-[0.22em] text-white/60 mb-3 inline-flex items-center gap-2">
                <x-icon category="Interface" name="Book" class="w-5 h-5 shrink-0"/>
                {{ __('staff-orders-create-title') }}
            </h2>

            <form id="staffOrderCreateForm"
                  method="POST"
                  action="{{ route('staff.orders.store', ['locale' => $locale]) }}"
                  class="space-y-3">
                @csrf

                <input type="hidden" name="table_id" id="staff_table_id" value="{{ old('table_id') }}">

                <div class="rounded-2xl bg-black/40 border border-white/10 px-3 py-2 text-xs text-white/80 min-w-0">
                    <div class="inline-flex items-center gap-2 min-w-0">
                        <x-icon category="System" name="Window" class="w-5 h-5 shrink-0"/>
                        <span id="staffSelectedTableText" class="truncate">
                            {{ __('staff-orders-field-table-placeholder') }}
                        </span>
                    </div>
                </div>

                <noscript>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] uppercase tracking-[0.22em] text-white/60">
                            {{ __('staff-orders-field-table') }}
                        </label>
                        <select name="table_id"
                                class="w-full bg-black/60 border border-white/20 rounded-xl px-3 py-2 text-xs text-white">
                            <option value="">{{ __('staff-orders-field-table-placeholder') }}</option>
                            @foreach($tables as $table)
                                <option value="{{ $table->id }}" @selected(old('table_id') == $table->id)>
                                    {{ $table->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </noscript>

                <button id="staffCreateBtn"
                        type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full
                               border border-white/70 text-[11px] uppercase tracking-[0.22em] text-white
                               hover:bg-white hover:text-black transition disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    <x-icon category="Edit" name="Add_Plus_Circle" class="w-5 h-5 shrink-0"/>
                    <span class="leading-none">{{ __('staff-orders-create-submit') }}</span>
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const plan = document.getElementById('staffFloorPlan');
    const buttons = document.querySelectorAll('.js-staff-table');
    const tableIdInput = document.getElementById('staff_table_id');
    const textEl = document.getElementById('staffSelectedTableText');
    const submitBtn = document.getElementById('staffCreateBtn');

    let selectedBtn = null;

    const setSelected = (btn) => {
        if (!btn || btn.dataset.disabled === '1') return;

        if (selectedBtn && selectedBtn !== btn) {
            selectedBtn.classList.remove('ring-2', 'ring-white');
        }

        btn.classList.add('ring-2', 'ring-white');
        selectedBtn = btn;

        tableIdInput.value = btn.dataset.id;

        const name = btn.dataset.name || '';
        const cap  = btn.dataset.capacity || '';
        textEl.textContent = cap
            ? (`{{ __('staff-orders-table-label') }}` + ' ' + name + ' · ' + cap)
            : (`{{ __('staff-orders-table-label') }}` + ' ' + name);

        submitBtn.disabled = false;
    };

    buttons.forEach(btn => btn.addEventListener('click', () => setSelected(btn)));

    // Responsive scale
    function setupResponsiveFloorPlan(planEl, selector) {
        if (!planEl) return;
        const tables = planEl.querySelectorAll(selector);
        if (!tables.length) return;

        const designWidth = 1200;

        const applyScale = () => {
            const planWidth = planEl.clientWidth || 1;
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
            ro.observe(planEl);
        } else {
            window.addEventListener('resize', applyScale);
        }
    }

    setupResponsiveFloorPlan(plan, '.js-staff-table');

    // Если был old(table_id) — восстановим
    if (tableIdInput && tableIdInput.value) {
        const oldBtn = Array.from(buttons).find(b => String(b.dataset.id) === String(tableIdInput.value));
        if (oldBtn) setSelected(oldBtn);
    }
});
</script>
@endpush
