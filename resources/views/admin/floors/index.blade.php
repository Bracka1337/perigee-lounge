@extends('layouts.admin')

@php
    $locale = app()->getLocale();
    $inputClass = 'w-full bg-ground/60 border border-line rounded-xl px-3 py-2.5 text-sm text-ink focus:outline-none focus:border-nebula focus:ring-1 focus:ring-nebula/70';
    $ghostButton = 'inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-line-strong px-4 text-xs text-ink-soft transition hover:bg-line hover:text-ink';
    $nameFields = ['en' => __('admin-floors-name-en'), 'lv' => __('admin-floors-name-lv'), 'ru' => __('admin-floors-name-ru')];
@endphp

@section('content')
    <div class="mb-8">
        <h1 class="logo-font text-2xl md:text-3xl lg:text-4xl mb-2">{{ __('admin-floors-title') }}</h1>
        <p class="text-xs md:text-sm text-ink-muted">{{ __('admin-floors-subtitle') }}</p>
    </div>

    @if (session('status'))
        <div class="mb-6 bg-emerald-950/60 border border-emerald-500/70 text-emerald-100 text-xs md:text-sm rounded-xl px-4 py-3" role="status">
            <p class="font-semibold mb-1 text-xs tracking-wide uppercase text-emerald-200">{{ __('admin-floors-alert-success-title') }}</p>
            <p>{{ session('status') }}</p>
        </div>
    @endif

    @if (session('error') || $errors->any())
        <div class="mb-6 bg-red-950/60 border border-red-500/60 text-red-100 text-xs md:text-sm rounded-xl px-4 py-3" role="alert">
            <p class="font-semibold mb-1 text-xs tracking-wide uppercase text-red-200">{{ __('admin-floors-alert-error-title') }}</p>
            @if (session('error'))
                <p>{{ session('error') }}</p>
            @endif
            @foreach (array_unique($errors->all()) as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,2.4fr)]">
        {{-- FLOORS --}}
        <div class="space-y-6">
            <div class="rounded-2xl border border-line bg-ground/70 p-4">
                <h2 class="mb-3 text-xs text-ink-muted">{{ __('admin-floors-list-title') }}</h2>

                @if ($floors->isEmpty())
                    <p class="text-sm text-ink-muted">{{ __('admin-floors-empty') }}</p>
                @else
                    <ul class="space-y-2">
                        @foreach ($floors as $floor)
                            @php
                                $isActive = $activeFloor !== null && $floor->id === $activeFloor->id;
                            @endphp
                            <li @class(['flex items-center gap-2 rounded-xl border px-3 py-1', 'border-nebula bg-nebula/10' => $isActive, 'border-line' => ! $isActive])>
                                <a href="{{ route('admin.floors.index', ['locale' => $locale, 'floor' => $floor->id]) }}"
                                   class="flex min-h-11 min-w-0 flex-1 flex-col justify-center"
                                   @if ($isActive) aria-current="true" @endif>
                                    <span class="truncate text-sm text-ink">{{ $floor->t('name') }}</span>
                                    <span class="text-xs text-ink-muted">{{ __('admin-floors-rooms-count', ['count' => $floor->rooms->count()]) }}</span>
                                </a>

                                @foreach (['up' => ['↑', __('admin-floors-move-up'), $loop->first], 'down' => ['↓', __('admin-floors-move-down'), $loop->last]] as $direction => [$arrow, $label, $atEdge])
                                    <form method="POST" action="{{ route('admin.floors.move', ['locale' => $locale, 'floor' => $floor]) }}">
                                        @csrf
                                        <input type="hidden" name="direction" value="{{ $direction }}">
                                        <button type="submit" title="{{ $label }}" aria-label="{{ $label }}: {{ $floor->t('name') }}"
                                                class="flex h-11 w-11 items-center justify-center rounded-full border border-line text-ink-soft transition hover:border-line-strong hover:text-ink disabled:cursor-not-allowed disabled:opacity-30"
                                                @disabled($atEdge)>
                                            <span aria-hidden="true">{{ $arrow }}</span>
                                        </button>
                                    </form>
                                @endforeach
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.floors.store', ['locale' => $locale]) }}"
                  class="space-y-3 rounded-2xl border border-line bg-ground/70 p-4">
                @csrf
                <h2 class="text-xs text-ink-muted">{{ __('admin-floors-add-title') }}</h2>

                @foreach ($nameFields as $code => $label)
                    <div class="space-y-1.5">
                        <label for="new_floor_name_{{ $code }}" class="block text-xs text-ink-muted">{{ $label }}</label>
                        <input type="text" id="new_floor_name_{{ $code }}" name="name[{{ $code }}]" maxlength="60"
                               class="{{ $inputClass }}" @required($code === 'en')>
                    </div>
                @endforeach

                <p class="text-xs text-ink-muted">{{ __('admin-floors-name-hint') }}</p>

                <button type="submit" class="{{ $ghostButton }}">{{ __('admin-floors-add-button') }}</button>
            </form>
        </div>

        {{-- SELECTED FLOOR --}}
        @if ($activeFloor !== null)
            @php
                $tableCounts = $activeFloor->tables->countBy('room_id');
                $editorData = [
                    'rooms' => $activeFloor->rooms->map(fn ($room) => [
                        'id' => $room->id,
                        'name' => ['en' => $room->name['en'] ?? '', 'lv' => $room->name['lv'] ?? '', 'ru' => $room->name['ru'] ?? ''],
                        'kind' => $room->kind->value,
                        'color' => $room->color->value,
                        'x' => $room->x,
                        'y' => $room->y,
                        'width' => $room->width,
                        'height' => $room->height,
                        'tables' => $tableCounts->get($room->id, 0),
                    ])->values(),
                    'draft' => old('rooms_payload'),
                    'colors' => collect($colors)->mapWithKeys(fn ($color) => [$color->value => $color->hex()]),
                    'minSize' => \App\Http\Requests\Admin\RoomLayoutRequest::MIN_SIZE,
                    'deleteUrl' => route('admin.rooms.destroy', ['locale' => $locale, 'room' => 0]),
                    'labels' => [
                        'newRoom' => __('admin-rooms-new-name'),
                        'tables' => __('admin-rooms-tables-count', ['count' => '__COUNT__']),
                        'delete' => __('admin-rooms-delete-button'),
                        'confirmDelete' => __('admin-rooms-delete-confirm'),
                    ],
                ];
            @endphp

            <div class="space-y-6 min-w-0" id="roomEditor">
                {{-- PLAN EDITOR --}}
                <div class="rounded-2xl border border-line bg-ground/70 p-4 overflow-hidden">
                    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-xs text-ink-muted">{{ __('admin-rooms-plan-title', ['floor' => $activeFloor->t('name')]) }}</h2>
                        <p class="text-xs text-ink-muted sm:max-w-md sm:text-right">{{ __('admin-rooms-plan-help') }}</p>
                    </div>

                    <div class="overflow-x-auto">
                        <div data-room-plan data-floor-plan="auto"
                             class="room-editor relative w-full min-w-[640px] aspect-[16/14] select-none overflow-hidden rounded-2xl border border-line bg-surface">
                            @foreach ($activeFloor->tables as $table)
                                <div class="room-editor__table"
                                     data-base-width="{{ $table->width }}" data-base-height="{{ $table->height }}"
                                     style="left: {{ $table->pos_x }}%; top: {{ $table->pos_y }}%; width: {{ $table->width }}px; height: {{ $table->height }}px; transform: translate(-50%, -50%) rotate({{ $table->rotation }}deg);">
                                    {{ $table->name }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <button type="button" data-room-add class="{{ $ghostButton }}">{{ __('admin-rooms-add-button') }}</button>

                        <div class="flex flex-wrap items-center gap-3">
                            <p data-room-dirty class="hidden text-xs text-ink-muted" role="status">{{ __('admin-rooms-unsaved-hint') }}</p>

                            <form method="POST" data-room-form
                                  action="{{ route('admin.floors.rooms.update', ['locale' => $locale, 'floor' => $activeFloor]) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="rooms_payload">
                                <button type="submit"
                                        class="inline-flex min-h-11 items-center justify-center rounded-full bg-nebula px-5 text-xs font-semibold text-on-nebula transition hover:bg-ink">
                                    {{ __('admin-rooms-save-button') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- SELECTED ROOM --}}
                <div class="rounded-2xl border border-line bg-ground/70 p-4">
                    <h2 class="mb-3 text-xs text-ink-muted">{{ __('admin-rooms-selected-title') }}</h2>

                    <p data-room-empty class="text-sm text-ink-muted">{{ __('admin-rooms-none-selected') }}</p>

                    <div data-room-panel class="hidden space-y-4">
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                            @foreach ($nameFields as $code => $label)
                                <div class="space-y-1.5">
                                    <label for="room_name_{{ $code }}" class="block text-xs text-ink-muted">{{ $label }}</label>
                                    <input type="text" id="room_name_{{ $code }}" data-room-name="{{ $code }}" maxlength="60" class="{{ $inputClass }}">
                                </div>
                            @endforeach
                        </div>

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                            <div class="space-y-1.5">
                                <label for="room_kind" class="block text-xs text-ink-muted">{{ __('admin-rooms-field-kind') }}</label>
                                <select id="room_kind" data-room-kind class="{{ $inputClass }}">
                                    @foreach ($kinds as $kind)
                                        <option value="{{ $kind->value }}">{{ __($kind->labelKey()) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <fieldset class="md:col-span-2">
                                <legend class="mb-1.5 block text-xs text-ink-muted">{{ __('admin-rooms-field-color') }}</legend>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($colors as $color)
                                        <label class="room-editor__color" title="{{ __($color->labelKey()) }}">
                                            <input type="radio" name="room_color" value="{{ $color->value }}" data-room-color class="sr-only">
                                            <span style="--room: {{ $color->hex() }};"></span>
                                            <span class="sr-only">{{ __($color->labelKey()) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line pt-4">
                            <p class="text-xs text-ink-muted">
                                <span data-room-tables></span>
                                <span data-room-delete-help class="hidden">· {{ __('admin-rooms-delete-help') }}</span>
                            </p>
                            <button type="button" data-room-delete class="{{ $ghostButton }} disabled:cursor-not-allowed disabled:opacity-40">
                                {{ __('admin-rooms-delete-button') }}
                            </button>
                        </div>
                    </div>

                    <form method="POST" data-room-delete-form class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>

                {{-- FLOOR NAME AND DELETE --}}
                <div class="rounded-2xl border border-line bg-ground/70 p-4">
                    <h2 class="mb-3 text-xs text-ink-muted">{{ __('admin-floors-rename-title') }}</h2>

                    <form method="POST" action="{{ route('admin.floors.update', ['locale' => $locale, 'floor' => $activeFloor]) }}" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                            @foreach ($nameFields as $code => $label)
                                <div class="space-y-1.5">
                                    <label for="floor_name_{{ $code }}" class="block text-xs text-ink-muted">{{ $label }}</label>
                                    <input type="text" id="floor_name_{{ $code }}" name="name[{{ $code }}]" maxlength="60"
                                           value="{{ $activeFloor->name[$code] ?? '' }}" class="{{ $inputClass }}" @required($code === 'en')>
                                </div>
                            @endforeach
                        </div>
                        <button type="submit" class="{{ $ghostButton }}">{{ __('admin-floors-save-button') }}</button>
                    </form>

                    <form method="POST" action="{{ route('admin.floors.destroy', ['locale' => $locale, 'floor' => $activeFloor]) }}"
                          class="mt-4 flex flex-wrap items-center gap-3 border-t border-line pt-4" data-confirm-form>
                        @csrf
                        @method('DELETE')
                        <button type="button" data-confirm-ask class="{{ $ghostButton }}">{{ __('admin-floors-delete-button') }}</button>
                        <button type="submit" data-confirm-yes
                                class="hidden min-h-11 items-center justify-center rounded-full border border-red-500/80 bg-red-950/40 px-4 text-xs text-red-100 transition hover:bg-red-900/50">
                            {{ __('admin-floors-delete-confirm') }}
                        </button>
                    </form>
                </div>
            </div>

            <script type="application/json" id="roomEditorData">@json($editorData)</script>
        @endif
    </div>
@endsection

@push('scripts')
    @vite('resources/js/room-editor.js')
@endpush
