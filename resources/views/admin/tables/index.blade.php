@extends('layouts.admin')

@section('content')
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="logo-font text-2xl md:text-3xl lg:text-4xl mb-2">
                {{ __('admin-tables-title') }}
            </h1>
            <p class="text-xs md:text-sm text-ink-muted">
                {{ __('admin-tables-subtitle') }}
            </p>
        </div>

<a href="{{ route('admin.tables.create', ['locale' => app()->getLocale()]) }}"
   class="inline-flex justify-center items-center gap-2 px-4 py-2 rounded-full border border-red-500/70
          bg-nebula text-xs md:text-xs text-on-nebula
          hover:bg-ink transition">
    <x-icon category="System" name="Window" class="w-5 h-5"/>
    <span>{{ __('admin-tables-create-button') }}</span>
</a>

    </div>

    @if (session('status'))
        <div class="mb-6 bg-emerald-950/60 border border-emerald-500/70 text-emerald-100 text-xs md:text-sm rounded-xl px-4 py-3">
            <p class="font-semibold mb-1 text-xs tracking-wide uppercase text-emerald-200">
                {{ __('admin-tables-alert-success-title') }}
            </p>
            <p>{{ session('status') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-[2fr,1fr] gap-6 mb-8">
        {{-- PLAN --}}
        <div class="rounded-2xl border border-line bg-ground/70 p-4 overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                <h2 class="text-xs text-ink-muted">
                    {{ __('admin-tables-plan-title') }}
                </h2>
                <p class="text-xs text-ink-muted">
                    {{ __('admin-tables-plan-help') }}
                </p>
            </div>

            <div class="overflow-x-auto">
<div id="tables-plan"
                 class="relative w-full min-w-[640px] aspect-[16/14] bg-surface rounded-2xl overflow-hidden border border-line">
                @foreach($tables as $table)
                    <div class="absolute js-table-item"
                         data-id="{{ $table->id }}"
                         data-pos-x="{{ $table->pos_x }}"
                         data-pos-y="{{ $table->pos_y }}"
                         data-rotation="{{ $table->rotation }}"
                         data-base-width="{{ $table->width }}"
                         data-base-height="{{ $table->height }}"
                         data-active="{{ $table->is_active ? '1' : '0' }}"
                         style="
                             left: {{ $table->pos_x }}%;
                             top: {{ $table->pos_y }}%;
                             width: {{ $table->width }}px;
                             height: {{ $table->height }}px;
                             transform: translate(-50%, -50%) rotate({{ $table->rotation }}deg);
                         ">
                        <div class="relative w-full h-full rounded-xl border text-xs text-center flex flex-col items-center justify-center cursor-move table-inner
                                    {{ $table->is_active ? 'border-line-strong bg-line' : 'border-red-500/50 bg-red-950/30 opacity-50' }}"
                             @if(!$table->is_active)
                                 style="background: repeating-linear-gradient(45deg, rgba(153, 27, 27, 0.3), rgba(153, 27, 27, 0.3) 10px, transparent 10px, transparent 20px), rgba(153, 27, 27, 0.2);"
                             @endif>
                            <span class="font-semibold">{{ $table->name }}</span>
                            @if(!$table->is_active)
                                <span class="text-[8px] text-red-300 font-bold mt-1">INACTIVE</span>
                            @endif

                            <div class="absolute -right-1 -bottom-1 w-3 h-3 rounded-full bg-ink shadow cursor-nwse-resize js-resize-handle"></div>
                            <div class="absolute left-1/2 -top-3 -translate-x-1/2 w-3 h-3 rounded-full bg-red-400 shadow cursor-crosshair js-rotate-handle"></div>
                        </div>
                    </div>
                @endforeach
            </div>
</div>

            <div class="mt-4 flex justify-end">
                <button type="button" id="save-layout"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-line-strong
                               text-xs text-ink-soft hover:bg-line hover:text-ink transition">
                    <x-icon category="System" name="Save" class="w-5 h-5 shrink-0"/>
                    <span class="leading-none">{{ __('admin-tables-plan-save-button') }}</span>
                </button>
            </div>

            <form id="tables-layout-form"
                  action="{{ route('admin.tables.layout.update', ['locale' => app()->getLocale()]) }}"
                  method="POST" class="hidden">
                @csrf
                <input type="hidden" name="layout_payload" id="layoutPayload">
            </form>
        </div>

        {{-- OPENING HOURS --}}
        <div class="rounded-2xl border border-line bg-ground/70 p-4 overflow-hidden">
            <h2 class="text-xs text-ink-muted mb-3">
                {{ __('admin-tables-opening-hours-title') }}
            </h2>

            <form action="{{ route('admin.tables.opening-hours.update', ['locale' => app()->getLocale()]) }}" method="POST" class="space-y-2">
                @csrf
                @php
                    $weekdays = [
                        1 => __('admin-weekday-mon'),
                        2 => __('admin-weekday-tue'),
                        3 => __('admin-weekday-wed'),
                        4 => __('admin-weekday-thu'),
                        5 => __('admin-weekday-fri'),
                        6 => __('admin-weekday-sat'),
                        0 => __('admin-weekday-sun'),
                    ];
                @endphp

                @foreach($weekdays as $dayIndex => $label)
                    @php $row = $hours[$dayIndex] ?? null; @endphp

                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 text-xs min-w-0">
                        <div class="sm:w-20 text-ink-muted shrink-0">
                            {{ $label }}
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <input type="checkbox"
                                   name="hours[{{ $dayIndex }}][is_open]"
                                   value="1"
                                   class="w-4 h-4 rounded border-line-strong bg-ground/60"
                                   {{ $row && $row->is_open ? 'checked' : '' }}>
                            <span class="text-ink-muted">{{ __('admin-tables-opening-hours-open') }}</span>
                        </div>

                        <div class="flex items-center gap-2 min-w-0 w-full sm:w-auto">
                            <input type="time"
                                   name="hours[{{ $dayIndex }}][opens_at]"
                                   value="{{ $row && $row->opens_at ? \Illuminate\Support\Str::of($row->opens_at)->substr(0,5) : '' }}"
                                   class="min-w-0 w-full sm:w-24 bg-ground/60 border border-line rounded-lg px-2 py-1 text-xs text-ink">
                            <span class="text-ink-muted shrink-0">—</span>
                            <input type="time"
                                   name="hours[{{ $dayIndex }}][closes_at]"
                                   value="{{ $row && $row->closes_at ? \Illuminate\Support\Str::of($row->closes_at)->substr(0,5) : '' }}"
                                   class="min-w-0 w-full sm:w-24 bg-ground/60 border border-line rounded-lg px-2 py-1 text-xs text-ink">
                        </div>
                    </div>
                @endforeach

                <div class="pt-3">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-line-strong
                                   text-xs text-ink-soft hover:bg-line hover:text-ink transition">
                        <x-icon category="System" name="Save" class="w-5 h-5 shrink-0"/>
                        <span class="leading-none">{{ __('admin-tables-opening-hours-save-button') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLES LIST --}}
    <div class="bg-surface border border-line rounded-2xl overflow-hidden">
        {{-- MOBILE --}}
        <div class="md:hidden p-4 space-y-3">
            @forelse($tables as $table)
                <div class="rounded-2xl bg-ground/40 border border-line px-4 py-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-medium text-ink truncate">{{ $table->name }}</div>
                            <div class="text-xs text-ink-muted">
                                {{ $table->is_active ? __('admin-tables-status-active') : __('admin-tables-status-inactive') }}
                            </div>
                            <div class="mt-2 text-xs text-ink-muted">
                                <span class="text-ink-muted">{{ __('admin-tables-capacity-label') }}</span>:
                                <span class="text-ink-soft">{{ $table->capacity }}</span>
                            </div>
                            <div class="text-xs text-ink-muted">
                                <span class="text-ink-muted">W/H/Rot</span>:
                                {{ $table->width }} × {{ $table->height }} / {{ $table->rotation }}°
                            </div>
                        </div>

                        <div class="shrink-0 flex items-center gap-2">
                            <a href="{{ route('admin.tables.edit', ['locale' => app()->getLocale(), 'table' => $table]) }}"
                               class="inline-flex items-center justify-center w-10 h-10 rounded-full border border-line
                                      text-ink-soft hover:text-ink hover:border-line-strong hover:bg-surface transition"
                               title="{{ __('admin-tables-edit-button') }}">
                                <x-icon category="Edit" name="Edit_Pencil_02" class="w-5 h-5"/>
                                <span class="sr-only">{{ __('admin-tables-edit-button') }}</span>
                            </a>

                            <form method="POST"
                                  id="delete-table-{{ $table->id }}"
                                  action="{{ route('admin.tables.destroy', ['locale' => app()->getLocale(), 'table' => $table]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                        class="inline-flex items-center justify-center w-10 h-10 rounded-full border border-red-500/70
                                               text-red-300 hover:text-red-100 hover:border-red-400 hover:bg-red-500/20 transition js-delete-table-btn"
                                        title="{{ __('admin-tables-delete-button') }}"
                                        data-table-name="{{ $table->name }}"
                                        data-form-id="delete-table-{{ $table->id }}">
                                    <x-icon category="Interface" name="Trash_Empty" class="w-5 h-5"/>
                                    <span class="sr-only">{{ __('admin-tables-delete-button') }}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-ground/40 border border-line px-4 py-6 text-center text-ink-muted text-sm">
                    {{ __('admin-tables-empty') }}
                </div>
            @endforelse
        </div>

        {{-- DESKTOP --}}
        <div class="hidden md:block">
            <table class="w-full text-sm">
                <thead class="bg-surface text-xs text-ink-muted">
                <tr>
                    <th class="px-5 py-3 text-left">{{ __('admin-tables-list-title') }}</th>
                    <th class="px-5 py-3 text-left">{{ __('admin-tables-capacity-label') }}</th>
                    <th class="px-5 py-3 text-left">W/H/Rot</th>
                    <th class="px-5 py-3 text-right">{{ __('admin-tables-table-actions') }}</th>
                </tr>
                </thead>

                <tbody>
                @forelse($tables as $table)
                    <tr class="border-t border-line hover:bg-surface transition">
                        <td class="px-5 py-3 align-top">
                            <div class="font-medium text-ink">
                                {{ $table->name }}
                            </div>
                            <div class="text-xs text-ink-muted">
                                {{ $table->is_active ? __('admin-tables-status-active') : __('admin-tables-status-inactive') }}
                            </div>
                        </td>

                        <td class="px-5 py-3 align-top text-ink-soft">
                            {{ $table->capacity }}
                        </td>

                        <td class="px-5 py-3 align-top text-ink-muted text-xs">
                            {{ $table->width }} × {{ $table->height }} / {{ $table->rotation }}°
                        </td>

                        <td class="px-5 py-3 align-top">
                            <div class="flex justify-end items-center gap-2">
                                <a href="{{ route('admin.tables.edit', ['locale' => app()->getLocale(), 'table' => $table]) }}"
                                   class="inline-flex items-center justify-center w-10 h-10 rounded-full border border-line
                                          text-ink-soft hover:text-ink hover:border-line-strong hover:bg-surface transition"
                                   title="{{ __('admin-tables-edit-button') }}">
                                    <x-icon category="Edit" name="Edit_Pencil_02" class="w-5 h-5"/>
                                    <span class="sr-only">{{ __('admin-tables-edit-button') }}</span>
                                </a>

                                <form method="POST"
                                      id="delete-table-{{ $table->id }}"
                                      action="{{ route('admin.tables.destroy', ['locale' => app()->getLocale(), 'table' => $table]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            class="inline-flex items-center justify-center w-10 h-10 rounded-full border border-red-500/70
                                                   text-red-300 hover:text-red-100 hover:border-red-400 hover:bg-red-500/20 transition js-delete-table-btn"
                                            title="{{ __('admin-tables-delete-button') }}"
                                            data-table-name="{{ $table->name }}"
                                            data-form-id="delete-table-{{ $table->id }}">
                                        <x-icon category="Interface" name="Trash_Empty" class="w-5 h-5"/>
                                        <span class="sr-only">{{ __('admin-tables-delete-button') }}</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-6 text-center text-ink-muted">
                            {{ __('admin-tables-empty') }}
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="deleteTableModal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-ground/70 backdrop-blur">
        <div class="max-w-md w-full mx-4 bg-ground/95 border border-red-500/50 rounded-2xl p-6">
            <div class="mb-3 flex items-center gap-3">
                <div class="w-9 h-9 rounded-full border border-red-500/70 flex items-center justify-center">
                    <span class="text-red-400 text-lg">!</span>
                </div>
                <h2 class="text-base md:text-lg font-semibold text-ink tracking-wide">
                    {{ __('admin-tables-delete-modal-title') }}
                </h2>
            </div>

            <p class="text-sm text-ink-muted mb-4">
                {{ __('admin-tables-delete-modal-text-prefix') }}
                <span id="deleteTableName" class="font-semibold text-ink"></span>?
            </p>
            <p class="text-xs text-ink-muted mb-5">
                {{ __('admin-tables-delete-modal-text-note') }}
            </p>

            <div class="flex flex-wrap justify-end gap-3">
                <button type="button"
                        id="deleteTableCancelBtn"
                        class="px-5 py-2 rounded-full border border-line-strong
                               text-xs text-ink-muted
                               hover:text-ink hover:border-line-strong hover:bg-surface transition">
                    {{ __('admin-tables-delete-modal-cancel') }}
                </button>
                <button type="button"
                        id="deleteTableConfirmBtn"
                        class="px-5 py-2 rounded-full border border-red-500/80 bg-red-600/90
                               text-xs text-ink
                               hover:bg-red-500 transition">
                    {{ __('admin-tables-delete-modal-confirm') }}
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const plan = document.getElementById('tables-plan');
            if (!plan) return;

            const items = plan.querySelectorAll('.js-table-item');

            let activeEl = null;
            let selectedEl = null;
            let action = null;

            let startMouseX = 0;
            let startMouseY = 0;
            let startPosX = 0;
            let startPosY = 0;
            let startRotation = 0;
            let startMouseAngle = 0;
            let centerX = 0;
            let centerY = 0;

            let currentScale = 1;
            let startBaseWidth = 0;
            let startBaseHeight = 0;

            function highlightSelected(el) {
                if (selectedEl && selectedEl !== el) {
                    const prevInner = selectedEl.querySelector('.table-inner');
                    if (prevInner) prevInner.classList.remove('ring-2', 'ring-red-400');
                }
                selectedEl = el;
                const inner = selectedEl.querySelector('.table-inner');
                if (inner) inner.classList.add('ring-2', 'ring-red-400');
            }

            function clearHighlight(el) {
                const inner = el.querySelector('.table-inner');
                if (inner) inner.classList.remove('ring-2', 'ring-red-400');
            }

            function initBaseSizes() {
                items.forEach(el => {
                    const bw = parseFloat(el.dataset.baseWidth);
                    const bh = parseFloat(el.dataset.baseHeight);

                    if (isNaN(bw) || isNaN(bh)) {
                        const styleW = parseFloat(el.style.width);
                        const styleH = parseFloat(el.style.height);
                        if (!isNaN(styleW) && !isNaN(styleH)) {
                            el.dataset.baseWidth = styleW;
                            el.dataset.baseHeight = styleH;
                        }
                    }
                });
            }

            function applyScale() {
                const rect = plan.getBoundingClientRect();
                const planWidth = rect.width || 1;

                let scale = planWidth / 1200;
                if (scale < 0.35) scale = 0.35;
                if (scale > 1.2) scale = 1.2;

                currentScale = scale;

                items.forEach(el => {
                    const bw = parseFloat(el.dataset.baseWidth);
                    const bh = parseFloat(el.dataset.baseHeight);
                    if (!isNaN(bw) && !isNaN(bh)) {
                        el.style.width = (bw * currentScale) + 'px';
                        el.style.height = (bh * currentScale) + 'px';
                    }
                });
            }

            initBaseSizes();
            applyScale();
            window.addEventListener('resize', applyScale);

            plan.addEventListener('mousedown', (e) => {
                if (e.button !== 0) return;

                const resizeHandle = e.target.closest('.js-resize-handle');
                const rotateHandle = e.target.closest('.js-rotate-handle');
                const tableEl = e.target.closest('.js-table-item');
                if (!tableEl || !plan.contains(tableEl)) return;

                if (resizeHandle) action = 'resize';
                else if (rotateHandle) action = 'rotate';
                else if (e.target.closest('.table-inner')) action = 'move';
                else return;

                highlightSelected(tableEl);

                const rect = tableEl.getBoundingClientRect();
                startMouseX = e.clientX;
                startMouseY = e.clientY;

                const match = (window.getComputedStyle(tableEl).transform || '').match(/rotate\(([-0-9.]+)deg\)/);
                startRotation = match ? parseFloat(match[1]) : parseFloat(tableEl.dataset.rotation ?? 0);

                const baseW = parseFloat(tableEl.dataset.baseWidth);
                const baseH = parseFloat(tableEl.dataset.baseHeight);
                startBaseWidth  = !isNaN(baseW) ? baseW : rect.width / currentScale;
                startBaseHeight = !isNaN(baseH) ? baseH : rect.height / currentScale;

                centerX = rect.left + rect.width / 2;
                centerY = rect.top + rect.height / 2;
                startMouseAngle = Math.atan2(e.clientY - centerY, e.clientX - centerX);

                startPosX = parseFloat(tableEl.dataset.posX ?? tableEl.dataset.posX ?? '0');
                startPosY = parseFloat(tableEl.dataset.posY ?? tableEl.dataset.posY ?? '0');

                activeEl = tableEl;
                document.body.classList.add('select-none');
                e.preventDefault();
            });

            document.addEventListener('mousemove', (e) => {
                if (!activeEl || !action) return;

                const planRect = plan.getBoundingClientRect();
                const dx = e.clientX - startMouseX;
                const dy = e.clientY - startMouseY;

                if (action === 'move') {
                    let newPosX = startPosX + (dx / planRect.width) * 100;
                    let newPosY = startPosY + (dy / planRect.height) * 100;

                    newPosX = Math.max(0, Math.min(100, newPosX));
                    newPosY = Math.max(0, Math.min(100, newPosY));

                    activeEl.style.left = newPosX + '%';
                    activeEl.style.top  = newPosY + '%';

                    activeEl.dataset.posX = newPosX.toFixed(2);
                    activeEl.dataset.posY = newPosY.toFixed(2);
                }

                if (action === 'resize') {
                    let newBaseWidth  = startBaseWidth  + (dx / currentScale);
                    let newBaseHeight = startBaseHeight + (dy / currentScale);

                    if (newBaseWidth < 10) newBaseWidth = 10;
                    if (newBaseHeight < 10) newBaseHeight = 10;

                    activeEl.dataset.baseWidth  = newBaseWidth;
                    activeEl.dataset.baseHeight = newBaseHeight;

                    activeEl.style.width  = (newBaseWidth  * currentScale) + 'px';
                    activeEl.style.height = (newBaseHeight * currentScale) + 'px';
                }

                if (action === 'rotate') {
                    const angleNow = Math.atan2(e.clientY - centerY, e.clientX - centerX);
                    const deltaAngle = angleNow - startMouseAngle;
                    let newRotation = startRotation + deltaAngle * 180 / Math.PI;

                    if (newRotation < 0) newRotation += 360;
                    if (newRotation >= 360) newRotation -= 360;

                    activeEl.style.transform = `translate(-50%, -50%) rotate(${newRotation}deg)`;
                    activeEl.dataset.rotation = newRotation;
                }
            });

            document.addEventListener('mouseup', () => {
                if (!activeEl) return;
                activeEl = null;
                action = null;
                document.body.classList.remove('select-none');
            });

            plan.addEventListener('click', (e) => {
                const el = e.target.closest('.js-table-item');
                if (!el || !plan.contains(el)) return;

                if (selectedEl && selectedEl !== el) clearHighlight(selectedEl);
                highlightSelected(el);
            });

            const saveButton  = document.getElementById('save-layout');
            const layoutForm  = document.getElementById('tables-layout-form');
            const layoutInput = document.getElementById('layoutPayload');

            saveButton?.addEventListener('click', () => {
                const itemsData = [];
                plan.querySelectorAll('.js-table-item').forEach(el => {
                    const rotation = parseFloat(el.dataset.rotation ?? 0);
                    const bw = parseFloat(el.dataset.baseWidth);
                    const bh = parseFloat(el.dataset.baseHeight);
                    if (isNaN(bw) || isNaN(bh)) return;

                    itemsData.push({
                        id: parseInt(el.dataset.id),
                        pos_x: parseFloat(el.dataset.posX ?? '0'),
                        pos_y: parseFloat(el.dataset.posY ?? '0'),
                        width:  Math.round(bw),
                        height: Math.round(bh),
                        rotation: Math.round(rotation),
                    });
                });

                layoutInput.value = JSON.stringify(itemsData);
                layoutForm.submit();
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('deleteTableModal');
            const nameSpan = document.getElementById('deleteTableName');
            const cancelBtn = document.getElementById('deleteTableCancelBtn');
            const confirmBtn = document.getElementById('deleteTableConfirmBtn');

            let currentFormId = null;

            const openModal = (tableName, formId) => {
                nameSpan.textContent = tableName || '';
                currentFormId = formId;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                currentFormId = null;
            };

            document.querySelectorAll('.js-delete-table-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const tableName = btn.getAttribute('data-table-name');
                    const formId = btn.getAttribute('data-form-id');
                    openModal(tableName, formId);
                });
            });

            cancelBtn?.addEventListener('click', closeModal);

            modal?.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });

            confirmBtn?.addEventListener('click', () => {
                if (!currentFormId) return;
                const form = document.getElementById(currentFormId);
                if (form) form.submit();
            });
        });
    </script>
@endpush
