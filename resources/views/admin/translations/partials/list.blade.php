@php
    $isPaginator = $translations instanceof \Illuminate\Pagination\LengthAwarePaginator
        || $translations instanceof \Illuminate\Pagination\Paginator;
@endphp

<div class="space-y-3">
    @forelse($translations as $tr)
        @php
            // максимально “живучее” получение данных, чтобы не завязаться на конкретную структуру
            $id  = data_get($tr, 'id');
            $key = data_get($tr, 'key') ?? data_get($tr, 'slug') ?? data_get($tr, 'name') ?? '—';

            $updatedAt = data_get($tr, 'updated_at');
            $updatedAtStr = $updatedAt
                ? \Illuminate\Support\Carbon::parse($updatedAt)->format('d.m.Y H:i')
                : null;

            $getLocaleValue = function ($obj, $loc) {
                // пробуем самые частые варианты хранения
                $v = data_get($obj, "text.$loc")
                    ?? data_get($obj, "value.$loc")
                    ?? data_get($obj, "values.$loc")
                    ?? data_get($obj, $loc);

                return is_string($v) ? $v : (is_null($v) ? '' : (string)$v);
            };
        @endphp

        <div class="rounded-2xl bg-ground/40 border border-line px-4 py-3">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-ink font-medium break-all">
                        {{ $key }}
                    </div>

                    @if($updatedAtStr)
                        <div class="mt-1 text-xs text-ink-muted">
                            {{ __('admin-translations-updated-at-label') }}: {{ $updatedAtStr }}
                        </div>
                    @endif
                </div>

                {{-- ACTIONS --}}
                <div class="shrink-0 flex items-center gap-2">
                    @if(\Illuminate\Support\Facades\Route::has('admin.translations.edit') && $id)
                        <a href="{{ route('admin.translations.edit', $tr) }}"
                           class="inline-flex items-center justify-center
                                  w-10 h-10 sm:w-auto sm:h-auto
                                  sm:px-3 sm:py-1
                                  rounded-full border border-line
                                  text-ink-muted hover:text-ink hover:border-line-strong hover:bg-surface transition">
                            <x-icon category="Edit" name="Edit_Pencil_02" class="w-5 h-5"/>
                            {{-- {{ __('admin-translations-edit-button') }} --}}
                        </a>
                    @endif

                    @if(\Illuminate\Support\Facades\Route::has('admin.translations.destroy') && $id)
                        <form method="POST" id="delete-translation-{{ $id }}" action="{{ route('admin.translations.destroy', $tr) }}">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                    class="inline-flex items-center justify-center
                                           w-10 h-10 sm:w-auto sm:h-auto
                                           sm:px-3 sm:py-1
                                           rounded-full border border-red-500/70
                                           text-red-300 hover:text-red-100 hover:bg-red-500/20 transition js-delete-translation-btn"
                                    data-key="{{ $key }}"
                                    data-form-id="delete-translation-{{ $id }}">
                                <x-icon category="Interface" name="Trash_Empty" class="w-5 h-5"/>
                                {{-- {{ __('admin-translations-delete-button') }} --}}
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($locales as $loc)
                    @php
                        $val = $getLocaleValue($tr, $loc);
                        $isDefault = ($loc === $default);
                    @endphp

                    <div class="rounded-xl border border-line bg-ground/30 px-3 py-2">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-xs
                                {{ $isDefault ? 'border-emerald-400/70 text-emerald-200 bg-emerald-500/10' : 'border-line text-ink-muted' }}">
                                {{ strtoupper($loc) }}
                            </span>

                            @if($isDefault)
                                <span class="text-xs text-emerald-200/80">
                                    {{ __('admin-translations-default-badge') }}
                                </span>
                            @endif
                        </div>

                        <div class="text-xs text-ink-soft break-words">
                            @if(trim($val) !== '')
                                {{ \Illuminate\Support\Str::limit($val, 160) }}
                            @else
                                <span class="text-ink-muted">
                                    {{ __('admin-translations-empty-value') }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="rounded-2xl bg-surface border border-line px-4 py-8 text-center text-ink-muted text-sm">
            {{ __('admin-translations-empty') }}
        </div>
    @endforelse

@if($translations instanceof \Illuminate\Pagination\LengthAwarePaginator && $translations->hasPages())
    @php
        // чтобы сохранялись фильтры/поиск (?q=...)
        $p = $translations->appends(request()->query());

        $current = $p->currentPage();
        $last = $p->lastPage();

        // окно страниц вокруг текущей
        $start = max(1, $current - 2);
        $end   = min($last, $current + 2);

        // чтобы в начале/конце было ровнее
        if ($current <= 3) { $start = 1; $end = min($last, 5); }
        if ($current >= $last - 2) { $end = $last; $start = max(1, $last - 4); }
    @endphp

    <div class="rounded-2xl bg-surface border border-line overflow-hidden mt-3">
        <div class="px-4 md:px-5 py-3 bg-ground/60">

            {{-- MOBILE --}}
            <div class="sm:hidden flex items-center justify-between gap-3">
                {{-- Prev --}}
                @if ($p->onFirstPage())
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl
                                 bg-ground/30 border border-line text-ink-muted cursor-not-allowed">
                        ‹
                    </span>
                @else
                    <a href="{{ $p->previousPageUrl() }}"
                       class="inline-flex items-center justify-center w-10 h-10 rounded-xl
                              bg-ground/40 border border-line text-ink-muted
                              hover:bg-line hover:text-ink hover:border-line-strong transition">
                        ‹
                    </a>
                @endif

                {{-- Center --}}
                <div class="flex flex-col items-center leading-tight">
                    <div class="text-xs text-ink-muted">PAGE</div>
                    <div class="text-sm font-semibold text-ink">{{ $current }} / {{ $last }}</div>
                </div>

                {{-- Next --}}
                @if ($p->hasMorePages())
                    <a href="{{ $p->nextPageUrl() }}"
                       class="inline-flex items-center justify-center w-10 h-10 rounded-xl
                              bg-ground/40 border border-line text-ink-muted
                              hover:bg-line hover:text-ink hover:border-line-strong transition">
                        ›
                    </a>
                @else
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl
                                 bg-ground/30 border border-line text-ink-muted cursor-not-allowed">
                        ›
                    </span>
                @endif
            </div>

            {{-- DESKTOP --}}
            <div class="hidden sm:flex items-center justify-between gap-4">
                <div class="text-sm text-ink-muted">
                    {!! __('pagination-showing') !!}
                    <span class="font-medium text-ink-soft">{{ $p->firstItem() }}</span>
                    {!! __('pagination-to') !!}
                    <span class="font-medium text-ink-soft">{{ $p->lastItem() }}</span>
                    {!! __('pagination-of') !!}
                    <span class="font-medium text-ink-soft">{{ $p->total() }}</span>
                    {!! __('pagination-results') !!}
                </div>

                <div class="inline-flex items-center rounded-2xl overflow-hidden bg-ground/40 border border-line">

                    {{-- Prev --}}
                    @if ($p->onFirstPage())
                        <span class="inline-flex items-center justify-center px-3 py-2
                                     bg-ground/30 border-r border-line text-ink-muted cursor-not-allowed">
                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.707 15.707a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 1.414L8.414 10l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    @else
                        <a href="{{ $p->previousPageUrl() }}"
                           class="group inline-flex items-center justify-center px-3 py-2
                                  bg-ground/40 border-r border-line text-ink-muted
                                  hover:bg-line hover:text-ink transition">
                            <svg class="w-5 h-5 text-ink-muted group-hover:text-ink" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.707 15.707a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 1.414L8.414 10l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                    @endif

                    {{-- First + dots --}}
                    @if ($start > 1)
                        <a href="{{ $p->url(1) }}"
                           class="inline-flex items-center justify-center px-4 py-2 border-r border-line
                                  text-ink-muted hover:text-ink hover:bg-line transition">
                            1
                        </a>

                        @if ($start > 2)
                            <span class="inline-flex items-center justify-center px-4 py-2 border-r border-line text-ink-muted select-none">
                                …
                            </span>
                        @endif
                    @endif

                    {{-- Window pages --}}
                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $current)
                            <span class="inline-flex items-center justify-center px-4 py-2 border-r border-line
                                         bg-nebula text-red-100 font-semibold">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $p->url($page) }}"
                               class="inline-flex items-center justify-center px-4 py-2 border-r border-line
                                      text-ink-muted hover:text-ink hover:bg-line transition">
                                {{ $page }}
                            </a>
                        @endif
                    @endfor

                    {{-- Dots + last --}}
                    @if ($end < $last)
                        @if ($end < $last - 1)
                            <span class="inline-flex items-center justify-center px-4 py-2 border-r border-line text-ink-muted select-none">
                                …
                            </span>
                        @endif

                        <a href="{{ $p->url($last) }}"
                           class="inline-flex items-center justify-center px-4 py-2 border-r border-line
                                  text-ink-muted hover:text-ink hover:bg-line transition">
                            {{ $last }}
                        </a>
                    @endif

                    {{-- Next --}}
                    @if ($p->hasMorePages())
                        <a href="{{ $p->nextPageUrl() }}"
                           class="group inline-flex items-center justify-center px-3 py-2
                                  bg-ground/40 text-ink-muted hover:bg-line hover:text-ink transition">
                            <svg class="w-5 h-5 text-ink-muted group-hover:text-ink" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.293 4.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L11.586 10 7.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                    @else
                        <span class="inline-flex items-center justify-center px-3 py-2
                                     bg-ground/30 text-ink-muted cursor-not-allowed">
                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.293 4.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L11.586 10 7.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    @endif

                </div>
            </div>

        </div>
    </div>
@endif
</div>
