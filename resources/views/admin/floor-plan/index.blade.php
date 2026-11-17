@extends('layouts.admin')

@php
    $locale = app()->getLocale();
    $input = 'w-full min-h-11 rounded-xl border border-line bg-ground/60 px-3 text-sm text-ink focus:border-nebula focus:outline-none focus:ring-1 focus:ring-nebula/70';
    $label = 'mb-1 block text-xs text-ink-muted';
    $ghost = 'inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-line-strong px-4 text-xs text-ink-soft transition hover:bg-line hover:text-ink disabled:cursor-not-allowed disabled:opacity-40';
    $tool = 'plan-tool inline-flex min-h-11 items-center gap-2 rounded-xl px-3 text-xs text-ink-soft transition hover:bg-line hover:text-ink aria-pressed:bg-nebula aria-pressed:text-on-nebula aria-pressed:font-semibold';
    $nameFields = ['en' => __('admin-floors-name-en'), 'lv' => __('admin-floors-name-lv'), 'ru' => __('admin-floors-name-ru')];
@endphp

@section('content')
    <div class="mb-6 flex flex-col gap-1">
        <h1 class="logo-font text-2xl md:text-3xl lg:text-4xl">{{ __('admin-plan-title') }}</h1>
        <p class="text-xs md:text-sm text-ink-muted">{{ __('admin-plan-subtitle') }}</p>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-xl border border-emerald-500/70 bg-emerald-950/60 px-4 py-3 text-xs text-emerald-100 md:text-sm" role="status">{{ session('status') }}</div>
    @endif

    @if (session('error') || $errors->any())
        <div class="mb-4 rounded-xl border border-red-500/60 bg-red-950/60 px-4 py-3 text-xs text-red-100 md:text-sm" role="alert">
            <p class="mb-1 font-semibold uppercase tracking-wide text-red-200">{{ __('admin-floors-alert-error-title') }}</p>
            @if (session('error'))
                <p>{{ session('error') }}</p>
            @endif
            @foreach (array_unique($errors->all()) as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- FLOORS --}}
    <div class="mb-4 flex flex-wrap items-center gap-2">
        @foreach ($floors as $floor)
            <a href="{{ route('admin.floor-plan', ['locale' => $locale, 'floor' => $floor->id]) }}" data-floor-link
               @class(['inline-flex min-h-11 items-center rounded-full border px-4 text-xs transition',
                       'border-nebula bg-nebula/10 text-ink' => $activeFloor?->id === $floor->id,
                       'border-line text-ink-muted hover:text-ink' => $activeFloor?->id !== $floor->id])
               @if ($activeFloor?->id === $floor->id) aria-current="page" @endif>
                {{ $floor->t('name') }}
            </a>
        @endforeach

        <details class="plan-popover relative">
            <summary class="{{ $ghost }} cursor-pointer list-none">+ {{ __('admin-floors-add-button') }}</summary>
            <form method="POST" action="{{ route('admin.floors.store', ['locale' => $locale]) }}"
                  class="absolute left-0 z-30 mt-2 w-80 space-y-3 rounded-2xl border border-line-strong bg-surface p-4 shadow-2xl">
                @csrf
                @foreach ($nameFields as $code => $text)
                    <div>
                        <label for="new_floor_{{ $code }}" class="{{ $label }}">{{ $text }}</label>
                        <input id="new_floor_{{ $code }}" name="name[{{ $code }}]" maxlength="60" class="{{ $input }}" @required($code === 'en')>
                    </div>
                @endforeach
                <p class="text-xs text-ink-muted">{{ __('admin-floors-name-hint') }}</p>
                <button type="submit" class="{{ $ghost }}">{{ __('admin-floors-add-button') }}</button>
            </form>
        </details>

        @if ($activeFloor)
            <details class="plan-popover relative">
                <summary class="{{ $ghost }} cursor-pointer list-none">{{ __('admin-plan-floor-settings') }}</summary>
                <div class="absolute left-0 z-30 mt-2 w-80 space-y-4 rounded-2xl border border-line-strong bg-surface p-4 shadow-2xl">
                    <form method="POST" action="{{ route('admin.floors.update', ['locale' => $locale, 'floor' => $activeFloor]) }}" class="space-y-3">
                        @csrf
                        @method('PUT')
                        @foreach ($nameFields as $code => $text)
                            <div>
                                <label for="floor_{{ $code }}" class="{{ $label }}">{{ $text }}</label>
                                <input id="floor_{{ $code }}" name="name[{{ $code }}]" maxlength="60" value="{{ $activeFloor->name[$code] ?? '' }}" class="{{ $input }}" @required($code === 'en')>
                            </div>
                        @endforeach
                        <button type="submit" class="{{ $ghost }}">{{ __('admin-floors-save-button') }}</button>
                    </form>

                    <div class="flex flex-wrap gap-2 border-t border-line pt-4">
                        @foreach (['up' => __('admin-floors-move-up'), 'down' => __('admin-floors-move-down')] as $direction => $text)
                            <form method="POST" action="{{ route('admin.floors.move', ['locale' => $locale, 'floor' => $activeFloor]) }}">
                                @csrf
                                <input type="hidden" name="direction" value="{{ $direction }}">
                                <button type="submit" class="{{ $ghost }}">{{ $text }}</button>
                            </form>
                        @endforeach
                    </div>

                    <form method="POST" action="{{ route('admin.floors.destroy', ['locale' => $locale, 'floor' => $activeFloor]) }}" data-confirm-form class="border-t border-line pt-4">
                        @csrf
                        @method('DELETE')
                        <p class="mb-2 text-xs text-ink-muted">{{ __('admin-plan-floor-delete-help') }}</p>
                        <button type="button" data-confirm-ask class="{{ $ghost }}">{{ __('admin-floors-delete-button') }}</button>
                        <button type="submit" data-confirm-yes class="hidden min-h-11 items-center rounded-full border border-red-500/80 bg-red-950/40 px-4 text-xs text-red-100">{{ __('admin-floors-delete-confirm') }}</button>
                    </form>
                </div>
            </details>
        @endif
    </div>

    @if (! $activeFloor)
        <div class="rounded-2xl border border-line bg-ground/70 p-8 text-center text-sm text-ink-muted">{{ __('admin-floors-empty') }}</div>
    @else
        <div id="planEditor" class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_340px]">
            {{-- CANVAS --}}
            <div class="min-w-0 rounded-2xl border border-line bg-ground/70 p-3">
                <div class="mb-3 flex flex-wrap items-center gap-2" role="toolbar" aria-label="{{ __('admin-plan-toolbar') }}">
                    <div class="flex gap-1 rounded-2xl border border-line p-1" role="group" aria-label="{{ __('admin-plan-tools') }}">
                        <button type="button" data-tool="select" aria-pressed="true" class="{{ $tool }}" title="{{ __('admin-plan-tool-select-help') }}">
                            <svg viewBox="0 0 20 20" class="h-4 w-4" aria-hidden="true"><path d="M4 2l11 8-5 1 3 6-2 1-3-6-4 3z" fill="currentColor"/></svg>
                            {{ __('admin-plan-tool-select') }} <kbd class="plan-kbd">V</kbd>
                        </button>
                        <button type="button" data-tool="room" aria-pressed="false" class="{{ $tool }}" title="{{ __('admin-plan-tool-room-help') }}">
                            <svg viewBox="0 0 20 20" class="h-4 w-4" aria-hidden="true"><rect x="3" y="4" width="14" height="12" rx="1.5" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                            {{ __('admin-plan-tool-room') }} <kbd class="plan-kbd">R</kbd>
                        </button>
                        <button type="button" data-tool="table" aria-pressed="false" class="{{ $tool }}" title="{{ __('admin-plan-tool-table-help') }}">
                            <svg viewBox="0 0 20 20" class="h-4 w-4" aria-hidden="true"><rect x="5" y="6" width="10" height="8" rx="2" fill="currentColor"/></svg>
                            {{ __('admin-plan-tool-table') }} <kbd class="plan-kbd">T</kbd>
                        </button>
                    </div>

                    <div class="flex gap-1">
                        <button type="button" data-action="undo" class="plan-icon-button" title="{{ __('admin-plan-undo') }} (Ctrl+Z)" aria-label="{{ __('admin-plan-undo') }}">&#8630;</button>
                        <button type="button" data-action="redo" class="plan-icon-button" title="{{ __('admin-plan-redo') }} (Ctrl+Shift+Z)" aria-label="{{ __('admin-plan-redo') }}">&#8631;</button>
                    </div>

                    <div class="flex gap-1" data-zoom-controls>
                        <button type="button" data-action="zoom-out" class="plan-icon-button" aria-label="{{ __('floor-plan-zoom-out') }}" title="{{ __('floor-plan-zoom-out') }}">&minus;</button>
                        <button type="button" data-action="fit" class="plan-icon-button px-3 text-xs" title="{{ __('floor-plan-zoom-fit-help') }}">{{ __('floor-plan-zoom-fit') }}</button>
                        <button type="button" data-action="zoom-in" class="plan-icon-button" aria-label="{{ __('floor-plan-zoom-in') }}" title="{{ __('floor-plan-zoom-in') }}">+</button>
                    </div>

                    <div class="flex gap-1 rounded-full border border-line p-0.5" role="group" aria-label="{{ __('lounge-view-toggle-label') }}">
                        <button type="button" data-view="2d" aria-pressed="true" class="min-h-10 rounded-full px-3 text-xs text-ink-muted aria-pressed:bg-nebula aria-pressed:font-semibold aria-pressed:text-on-nebula">{{ __('lounge-view-2d') }}</button>
                        <button type="button" data-view="3d" aria-pressed="false" class="min-h-10 rounded-full px-3 text-xs text-ink-muted aria-pressed:bg-nebula aria-pressed:font-semibold aria-pressed:text-on-nebula">{{ __('lounge-view-3d') }}</button>
                    </div>

                    <div class="ml-auto flex items-center gap-3">
                        <span data-save-state class="text-xs text-ink-muted" role="status"></span>
                        <form method="POST" action="{{ route('admin.floors.plan.update', ['locale' => $locale, 'floor' => $activeFloor]) }}" data-save-form>
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="plan_payload">
                            <button type="submit" data-action="save"
                                    class="inline-flex min-h-11 items-center rounded-full bg-nebula px-5 text-xs font-semibold text-on-nebula transition hover:bg-ink disabled:cursor-not-allowed disabled:opacity-50">
                                {{ __('admin-plan-save') }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-xl border border-line bg-surface" style="height: min(72vh, 760px); min-height: 420px;">
                    <svg data-plan-canvas class="plan-canvas block h-full w-full touch-none select-none" role="application" aria-label="{{ __('admin-plan-canvas-label') }}"></svg>
                    <div data-plan-3d class="absolute inset-0 hidden"></div>
                    <p data-tool-hint class="pointer-events-none absolute bottom-2 left-2 rounded-lg bg-ground/85 px-3 py-1.5 text-xs text-ink-soft"></p>
                    <p data-problems class="absolute right-2 top-2 hidden max-w-sm rounded-lg border border-red-500/60 bg-red-950/85 px-3 py-2 text-xs text-red-100" role="alert"></p>
                </div>
            </div>

            {{-- INSPECTOR --}}
            <aside class="rounded-2xl border border-line bg-ground/70 p-4 xl:sticky xl:top-24 xl:max-h-[calc(100vh-7rem)] xl:overflow-y-auto" aria-live="polite">
                {{-- Nothing selected --}}
                <div data-panel="none" class="space-y-4">
                    <div>
                        <h2 class="text-sm font-semibold text-ink">{{ $activeFloor->t('name') }}</h2>
                        <p data-floor-summary class="text-xs text-ink-muted"></p>
                    </div>
                    <div class="rounded-xl border border-line p-3">
                        <h3 class="mb-2 text-xs font-semibold text-ink">{{ __('admin-plan-help-title') }}</h3>
                        <ul class="space-y-1.5 text-xs text-ink-muted">
                            @foreach (['draw', 'table', 'move', 'resize', 'pan', 'zoom', 'keys'] as $tip)
                                <li>{{ __('admin-plan-help-'.$tip) }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        <h3 class="mb-2 text-xs text-ink-muted">{{ __('admin-plan-rooms') }}</h3>
                        <ul data-room-list class="flex flex-wrap gap-1"></ul>
                    </div>
                    <div>
                        <h3 class="mb-2 text-xs text-ink-muted">{{ __('admin-plan-tables') }}</h3>
                        <ul data-table-list class="flex flex-wrap gap-1"></ul>
                    </div>
                    @if ($studioEnabled)
                        <form method="POST" action="{{ route('admin.floors.photos.store', ['locale' => $locale]) }}" class="space-y-2 border-t border-line pt-4">
                            @csrf
                            <h3 class="text-xs text-ink-muted">{{ __('admin-floors-photos-title') }}</h3>
                            <p class="text-xs text-ink-muted">{{ __('admin-floors-photos-help') }}</p>
                            <button type="submit" class="{{ $ghost }}">{{ __('admin-floors-photos-button') }}</button>
                        </form>
                    @endif
                </div>

                {{-- Room --}}
                <div data-panel="room" class="hidden space-y-4">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="text-sm font-semibold text-ink">{{ __('admin-rooms-selected-title') }}</h2>
                        <button type="button" data-action="deselect" class="plan-icon-button text-xs" aria-label="{{ __('admin-plan-close') }}">&times;</button>
                    </div>
                    @foreach ($nameFields as $code => $text)
                        <div>
                            <label for="room_name_{{ $code }}" class="{{ $label }}">{{ $text }}</label>
                            <input id="room_name_{{ $code }}" data-room-field="name.{{ $code }}" maxlength="60" class="{{ $input }}">
                        </div>
                    @endforeach
                    <div>
                        <label for="room_kind" class="{{ $label }}">{{ __('admin-rooms-field-kind') }}</label>
                        <select id="room_kind" data-room-field="kind" class="{{ $input }}"></select>
                    </div>
                    <fieldset>
                        <legend class="{{ $label }}">{{ __('admin-rooms-field-color') }}</legend>
                        <div data-color-swatches class="flex flex-wrap gap-1"></div>
                    </fieldset>
                    <label class="flex min-h-11 items-start gap-3 rounded-xl border border-line p-3 text-xs text-ink-soft">
                        <input type="checkbox" data-room-field="is_bookable_whole" class="mt-0.5 h-5 w-5 rounded border-line-strong bg-ground/60">
                        <span>
                            <span class="block font-semibold text-ink">{{ __('admin-plan-room-bookable') }}</span>
                            {{ __('admin-plan-room-bookable-help') }}
                        </span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach (['x' => 'X', 'y' => 'Y', 'width' => __('admin-plan-width'), 'height' => __('admin-plan-depth')] as $field => $text)
                            <div>
                                <label for="room_{{ $field }}" class="{{ $label }}">{{ $text }} (m)</label>
                                <input id="room_{{ $field }}" type="number" step="0.25" data-room-field="{{ $field }}" class="{{ $input }}">
                            </div>
                        @endforeach
                    </div>
                    <div class="space-y-2 border-t border-line pt-4">
                        <p data-room-tables class="text-xs text-ink-muted"></p>
                        <button type="button" data-action="delete-room" class="{{ $ghost }}">{{ __('admin-rooms-delete-button') }}</button>
                        <p data-room-delete-help class="hidden text-xs text-ink-muted">{{ __('admin-rooms-delete-help') }}</p>
                    </div>
                </div>

                {{-- Table --}}
                <div data-panel="table" class="hidden space-y-4">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="text-sm font-semibold text-ink">{{ __('admin-plan-selected-table') }}</h2>
                        <button type="button" data-action="deselect" class="plan-icon-button text-xs" aria-label="{{ __('admin-plan-close') }}">&times;</button>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label for="table_name" class="{{ $label }}">{{ __('admin-tables-field-name') }}</label>
                            <input id="table_name" data-table-field="name" maxlength="20" class="{{ $input }}">
                        </div>
                        <div>
                            <label for="table_capacity" class="{{ $label }}">{{ __('admin-tables-field-capacity') }}</label>
                            <input id="table_capacity" type="number" min="1" max="20" data-table-field="capacity" class="{{ $input }}">
                        </div>
                        <div>
                            <label for="table_width" class="{{ $label }}">{{ __('admin-plan-width') }} (cm)</label>
                            <input id="table_width" type="number" min="40" max="2000" step="10" data-table-field="width" class="{{ $input }}">
                        </div>
                        <div>
                            <label for="table_height" class="{{ $label }}">{{ __('admin-plan-depth') }} (cm)</label>
                            <input id="table_height" type="number" min="40" max="2000" step="10" data-table-field="height" class="{{ $input }}">
                        </div>
                        <div class="col-span-2">
                            <label for="table_rotation" class="{{ $label }}">{{ __('admin-tables-field-rotation') }}</label>
                            <input id="table_rotation" type="number" min="0" max="359" step="15" data-table-field="rotation" class="{{ $input }}">
                        </div>
                    </div>
                    <label class="flex min-h-11 items-center gap-3 text-sm text-ink-soft">
                        <input type="checkbox" data-table-field="is_active" class="h-5 w-5 rounded border-line-strong bg-ground/60">
                        {{ __('admin-plan-table-active') }}
                    </label>
                    <p data-table-room class="text-xs text-ink-muted"></p>

                    <form method="POST" data-move-form class="space-y-2 border-t border-line pt-4">
                        @csrf
                        @method('PUT')
                        <label for="move_room" class="{{ $label }}">{{ __('admin-plan-move-floor') }}</label>
                        <select id="move_room" name="room_id" class="{{ $input }}"></select>
                        <input type="hidden" name="name">
                        <input type="hidden" name="capacity">
                        <input type="hidden" name="is_active">
                        <button type="submit" class="{{ $ghost }}">{{ __('admin-plan-move-button') }}</button>
                        <p data-move-help class="hidden text-xs text-ink-muted">{{ __('admin-plan-save-first') }}</p>
                    </form>

                    <form method="POST" data-delete-table-form class="space-y-2 border-t border-line pt-4">
                        @csrf
                        @method('DELETE')
                        <p class="text-xs text-ink-muted">{{ __('admin-plan-table-delete-help') }}</p>
                        <button type="button" data-action="delete-table" class="{{ $ghost }}">{{ __('admin-tables-delete-button') }}</button>
                        <button type="submit" data-delete-table-yes class="hidden min-h-11 items-center rounded-full border border-red-500/80 bg-red-950/40 px-4 text-xs text-red-100">{{ __('admin-plan-table-delete-confirm') }}</button>
                    </form>
                </div>
            </aside>
        </div>

        <script type="application/json" id="planEditorData">@json($editor)</script>
        <script type="application/json" id="loungeLayoutData">@json($lounge3d)</script>
        @php
            $editorText = [
            'newRoom' => __('admin-rooms-new-name'),
            'tables' => __('admin-rooms-tables-count', ['count' => '__COUNT__']),
            'summary' => __('admin-plan-summary', ['rooms' => '__ROOMS__', 'tables' => '__TABLES__']),
            'inRoom' => __('admin-plan-table-in-room', ['room' => '__ROOM__']),
            'outside' => __('admin-plan-table-outside', ['name' => '__NAME__']),
            'overlap' => __('admin-plan-rooms-overlap', ['first' => '__A__', 'second' => '__B__']),
            'unsaved' => __('admin-rooms-unsaved-hint'),
            'saved' => __('admin-plan-all-saved'),
            'leave' => __('admin-plan-leave-warning'),
            'hints' => [
                'select' => __('admin-plan-hint-select'),
                'room' => __('admin-plan-hint-room'),
                'table' => __('admin-plan-hint-table'),
                'view3d' => __('admin-plan-hint-3d'),
            ],
            'chooseRoom' => __('admin-plan-move-choose'),
            ];
        @endphp
        <script type="application/json" id="planEditorText">@json($editorText)</script>
    @endif
@endsection

@push('scripts')
    @vite('resources/js/plan-editor.js')
@endpush
