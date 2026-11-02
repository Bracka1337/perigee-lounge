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

        <div class="rounded-2xl bg-black/40 border border-white/10 px-4 py-3">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-white font-medium break-all">
                        {{ $key }}
                    </div>

                    @if($updatedAtStr)
                        <div class="mt-1 text-[11px] text-white/40">
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
                                  rounded-full border border-white/25
                                  text-white/70 hover:text-white hover:border-white/60 hover:bg-white/5 transition">
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

                    <div class="rounded-xl border border-white/10 bg-black/30 px-3 py-2">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[9px] uppercase tracking-[0.18em]
                                {{ $isDefault ? 'border-emerald-400/70 text-emerald-200 bg-emerald-500/10' : 'border-white/20 text-white/60' }}">
                                {{ strtoupper($loc) }}
                            </span>

                            @if($isDefault)
                                <span class="text-[9px] uppercase tracking-[0.18em] text-emerald-200/80">
                                    {{ __('admin-translations-default-badge') }}
                                </span>
                            @endif
                        </div>

                        <div class="text-[12px] text-white/80 break-words">
                            @if(trim($val) !== '')
                                {{ \Illuminate\Support\Str::limit($val, 160) }}
                            @else
                                <span class="text-white/35">
                                    {{ __('admin-translations-empty-value') }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="rounded-2xl bg-white/5 border border-white/10 px-4 py-8 text-center text-white/50 text-sm">
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

    <div class="rounded-2xl bg-white/5 border border-white/10 overflow-hidden mt-3">
        <div class="px-4 md:px-5 py-3 bg-black/60">

            {{-- MOBILE --}}
            <div class="sm:hidden flex items-center justify-between gap-3">
                {{-- Prev --}}
                @if ($p->onFirstPage())
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl
                                 bg-black/30 border border-white/10 text-white/25 cursor-not-allowed">
                        ‹
                    </span>
                @else
                    <a href="{{ $p->previousPageUrl() }}"
                       class="inline-flex items-center justify-center w-10 h-10 rounded-xl
                              bg-black/40 border border-white/15 text-white/70
                              hover:bg-white/10 hover:text-white hover:border-white/35 transition">
                        ‹
                    </a>
                @endif

                {{-- Center --}}
                <div class="flex flex-col items-center leading-tight">
                    <div class="text-[10px] tracking-[0.22em] uppercase text-white/40">PAGE</div>
                    <div class="text-sm font-semibold text-white">{{ $current }} / {{ $last }}</div>
                </div>

                {{-- Next --}}
                @if ($p->hasMorePages())
                    <a href="{{ $p->nextPageUrl() }}"
                       class="inline-flex items-center justify-center w-10 h-10 rounded-xl
                              bg-black/40 border border-white/15 text-white/70
                              hover:bg-white/10 hover:text-white hover:border-white/35 transition">
                        ›
                    </a>
                @else
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl
                                 bg-black/30 border border-white/10 text-white/25 cursor-not-allowed">
                        ›
                    </span>
                @endif
            </div>

            {{-- DESKTOP --}}
            <div class="hidden sm:flex items-center justify-between gap-4">
                <div class="text-sm text-white/60">
                    {!! __('pagination-showing') !!}
                    <span class="font-medium text-white/80">{{ $p->firstItem() }}</span>
                    {!! __('pagination-to') !!}
                    <span class="font-medium text-white/80">{{ $p->lastItem() }}</span>
                    {!! __('pagination-of') !!}
                    <span class="font-medium text-white/80">{{ $p->total() }}</span>
                    {!! __('pagination-results') !!}
                </div>

                <div class="inline-flex items-center rounded-2xl overflow-hidden bg-black/40 border border-white/15">

                    {{-- Prev --}}
                    @if ($p->onFirstPage())
                        <span class="inline-flex items-center justify-center px-3 py-2
                                     bg-black/30 border-r border-white/10 text-white/25 cursor-not-allowed">
                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.707 15.707a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 1.414L8.414 10l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    @else
                        <a href="{{ $p->previousPageUrl() }}"
                           class="group inline-flex items-center justify-center px-3 py-2
                                  bg-black/40 border-r border-white/15 text-white/70
                                  hover:bg-white/10 hover:text-white transition">
                            <svg class="w-5 h-5 text-white/60 group-hover:text-white" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.707 15.707a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 1.414L8.414 10l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                    @endif

                    {{-- First + dots --}}
                    @if ($start > 1)
                        <a href="{{ $p->url(1) }}"
                           class="inline-flex items-center justify-center px-4 py-2 border-r border-white/15
                                  text-white/70 hover:text-white hover:bg-white/10 transition">
                            1
                        </a>

                        @if ($start > 2)
                            <span class="inline-flex items-center justify-center px-4 py-2 border-r border-white/15 text-white/40 select-none">
                                …
                            </span>
                        @endif
                    @endif

                    {{-- Window pages --}}
                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $current)
                            <span class="inline-flex items-center justify-center px-4 py-2 border-r border-white/15
                                         bg-red-600/80 text-red-100 font-semibold">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $p->url($page) }}"
                               class="inline-flex items-center justify-center px-4 py-2 border-r border-white/15
                                      text-white/70 hover:text-white hover:bg-white/10 transition">
                                {{ $page }}
                            </a>
                        @endif
                    @endfor

                    {{-- Dots + last --}}
                    @if ($end < $last)
                        @if ($end < $last - 1)
                            <span class="inline-flex items-center justify-center px-4 py-2 border-r border-white/15 text-white/40 select-none">
                                …
                            </span>
                        @endif

                        <a href="{{ $p->url($last) }}"
                           class="inline-flex items-center justify-center px-4 py-2 border-r border-white/15
                                  text-white/70 hover:text-white hover:bg-white/10 transition">
                            {{ $last }}
                        </a>
                    @endif

                    {{-- Next --}}
                    @if ($p->hasMorePages())
                        <a href="{{ $p->nextPageUrl() }}"
                           class="group inline-flex items-center justify-center px-3 py-2
                                  bg-black/40 text-white/70 hover:bg-white/10 hover:text-white transition">
                            <svg class="w-5 h-5 text-white/60 group-hover:text-white" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.293 4.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L11.586 10 7.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                    @else
                        <span class="inline-flex items-center justify-center px-3 py-2
                                     bg-black/30 text-white/25 cursor-not-allowed">
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
