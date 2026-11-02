@extends('layouts.admin')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div class="min-w-0">
        <h1 class="logo-font text-3xl md:text-4xl mb-2">
            {{ __('logs-title') }}
        </h1>
        <p class="text-sm text-ink-muted">
            {{ __('logs-subtitle') }}
        </p>
    </div>
</div>

{{-- Фильтры --}}
<div class="mb-6 rounded-xl border border-line bg-ground/40 backdrop-blur-sm p-6">
    <form method="GET" action="{{ route('admin.logs.index') }}" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
        {{-- Фильтр по типу модели --}}
        <div>
            <label class="block text-xs text-ink-muted mb-2">{{ __('logs-filter-model-type') }}</label>
            <select name="model_type" class="w-full rounded-lg border border-line bg-ground/60 px-3 py-2 text-sm text-ink focus:border-line-strong focus:outline-none">
                <option value="">{{ __('logs-filter-all') }}</option>
                @foreach($modelTypes as $type => $label)
                    <option value="{{ $type }}" {{ request('model_type') == $type ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Фильтр по действию --}}
        <div>
            <label class="block text-xs text-ink-muted mb-2">{{ __('logs-filter-action') }}</label>
            <select name="action" class="w-full rounded-lg border border-line bg-ground/60 px-3 py-2 text-sm text-ink focus:border-line-strong focus:outline-none">
                <option value="">{{ __('logs-filter-all') }}</option>
                <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>{{ __('logs-action-created') }}</option>
                <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>{{ __('logs-action-updated') }}</option>
                <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>{{ __('logs-action-deleted') }}</option>
            </select>
        </div>

        {{-- Фильтр по ID объекта --}}
        <div>
            <label class="block text-xs text-ink-muted mb-2">{{ __('logs-filter-model-id') }}</label>
            <input type="number" name="model_id" value="{{ request('model_id') }}"
                   class="w-full rounded-lg border border-line bg-ground/60 px-3 py-2 text-sm text-ink placeholder:text-ink-muted focus:border-line-strong focus:outline-none"
                   placeholder="{{ __('logs-filter-all') }}">
        </div>

        {{-- Фильтр по дате от --}}
        <div>
            <label class="block text-xs text-ink-muted mb-2">{{ __('logs-filter-date-from') }}</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="w-full rounded-lg border border-line bg-ground/60 px-3 py-2 text-sm text-ink focus:border-line-strong focus:outline-none">
        </div>

        {{-- Фильтр по дате до --}}
        <div>
            <label class="block text-xs text-ink-muted mb-2">{{ __('logs-filter-date-to') }}</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="w-full rounded-lg border border-line bg-ground/60 px-3 py-2 text-sm text-ink focus:border-line-strong focus:outline-none">
        </div>

        <div class="md:col-span-3 lg:col-span-5 flex gap-3">
            <button type="submit" class="group inline-flex items-center gap-2 px-5 py-2.5 bg-surface hover:bg-line border border-line-strong hover:border-line-strong text-ink rounded-lg text-sm font-medium transition-all duration-300 ">
                <x-icon category="Interface" name="Filter" class="w-4 h-4 group-hover:scale-110 transition-transform"/>
                <span>{{ __('logs-filter-apply') }}</span>
            </button>
            <a href="{{ route('admin.logs.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-line hover:bg-line border border-line hover:border-line-strong text-ink-soft hover:text-ink rounded-lg text-sm transition-all duration-300  ">
                <x-icon category="Arrows" name="Refresh_CCW_01" class="w-4 h-4"/>
                <span>{{ __('logs-filter-reset') }}</span>
            </a>
        </div>
    </form>
</div>

{{-- Таблица логов --}}
<div class="rounded-xl border border-line bg-ground/40 backdrop-blur-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-line bg-surface">
                    <th class="px-4 py-3 text-left text-xs font-medium text-ink-muted">{{ __('logs-table-time') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-ink-muted">{{ __('logs-table-user') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-ink-muted">{{ __('logs-table-action') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-ink-muted">{{ __('logs-table-object') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-ink-muted">{{ __('logs-table-ip-browser') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-ink-muted">{{ __('logs-table-changes') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse($logs as $log)
                    <tr class="hover:bg-surface transition-colors">
                        {{-- Время --}}
                        <td class="px-4 py-3 text-ink-soft whitespace-nowrap">
                            <div class="text-xs">{{ $log->created_at->format('d.m.Y') }}</div>
                            <div class="text-xs text-ink-muted">{{ $log->created_at->format('H:i:s') }}</div>
                        </td>

                        {{-- Пользователь --}}
                        <td class="px-4 py-3 text-ink-soft">
                            @if($log->user)
                                <div class="text-xs">{{ $log->user->name }}</div>
                                <div class="text-xs text-ink-muted">{{ $log->user->email }}</div>
                            @else
                                <span class="text-xs text-ink-muted">{{ __('logs-system') }}</span>
                            @endif
                        </td>

                        {{-- Действие --}}
                        <td class="px-4 py-3">
                            @php
                                $actionColors = [
                                    'created' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/50',
                                    'updated' => 'bg-blue-500/20 text-blue-300 border-blue-500/50',
                                    'deleted' => 'bg-red-500/20 text-red-300 border-red-500/50',
                                ];
                                $colorClass = $actionColors[$log->action] ?? 'bg-line text-ink-muted border-line';
                            @endphp
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs border {{ $colorClass }}">
                                {{ $log->action_name }}
                            </span>
                        </td>

                        {{-- Объект --}}
                        <td class="px-4 py-3 text-ink-soft">
                            <div class="text-xs">{{ $log->model_name }}</div>
                            <div class="text-xs text-ink-muted">ID: {{ $log->model_id }}</div>
                        </td>

                        {{-- IP / Браузер --}}
                        <td class="px-4 py-3 text-ink-muted">
                            <div class="text-xs">{{ $log->ip_address ?? 'N/A' }}</div>
                            <div class="text-xs text-ink-muted truncate max-w-[200px]" title="{{ $log->user_agent }}">
                                @if($log->user_agent)
                                    @php
                                        // Определяем браузер из user agent
                                        $ua = $log->user_agent;
                                        if (str_contains($ua, 'Chrome')) {
                                            $browser = 'Chrome';
                                        } elseif (str_contains($ua, 'Firefox')) {
                                            $browser = 'Firefox';
                                        } elseif (str_contains($ua, 'Safari')) {
                                            $browser = 'Safari';
                                        } elseif (str_contains($ua, 'Edge')) {
                                            $browser = 'Edge';
                                        } else {
                                            $browser = 'Other';
                                        }
                                    @endphp
                                    {{ $browser }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </td>

                        {{-- Изменения --}}
                        <td class="px-4 py-3">
                            @if($log->action === 'created')
                                <button
                                    onclick="showChangesModal({{ $log->id }}, 'created')"
                                    class="text-xs text-emerald-300 hover:text-emerald-200 underline"
                                >
                                    <x-icon category="Interface" name="External_Link" class="w-6 h-6"/>
                                </button>
                            @elseif($log->action === 'updated')
                                @php
                                    $changes = $log->changes;
                                @endphp
                                @if(count($changes) > 0)
                                    <button
                                        onclick="showChangesModal({{ $log->id }}, 'updated')"
                                        class="text-xs text-blue-300 hover:text-blue-200 underline"
                                    >
                                        <x-icon category="Interface" name="External_Link" class="w-6 h-6"/>
                                    </button>
                                @else
                                    <span class="text-xs text-ink-muted">{{ __('logs-no-changes') }}</span>
                                @endif
                            @elseif($log->action === 'deleted')
                                <button
                                    onclick="showChangesModal({{ $log->id }}, 'deleted')"
                                    class="text-xs text-red-300 hover:text-red-200 underline"
                                >
                                    <x-icon category="Interface" name="External_Link" class="w-6 h-6"/>
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-ink-muted">
                            {{ __('logs-no-logs') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Пагинация --}}
@if(($logs instanceof \Illuminate\Pagination\LengthAwarePaginator || $logs instanceof \Illuminate\Pagination\Paginator) && $logs->hasPages())
    @php
        $p = $logs->appends(request()->query());

        $current = $p->currentPage();
        $last = $p->lastPage();

        $start = max(1, $current - 2);
        $end   = min($last, $current + 2);

        if ($current <= 3) { $start = 1; $end = min($last, 5); }
        if ($current >= $last - 2) { $end = $last; $start = max(1, $last - 4); }
    @endphp

    <div class="rounded-2xl bg-surface border border-line overflow-hidden mt-6">
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

{{-- Модальное окно для отображения изменений --}}
<div id="changesModal" class="hidden fixed inset-0 bg-ground/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-ground/90 border border-line rounded-xl max-w-4xl w-full max-h-[80vh] overflow-hidden flex flex-col">
        <div class="flex items-center justify-between p-6 border-b border-line">
            <h3 class="text-xl font-semibold text-ink" id="modalTitle">{{ __('logs-modal-title-updated') }}</h3>
            <button onclick="closeChangesModal()" class="text-ink-muted hover:text-ink transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto" id="modalContent">
            <!-- Содержимое загружается динамически -->
        </div>
    </div>
</div>

<script>
// Данные логов для модального окна
const logsData = @json($logsData);

// Переводы для JavaScript
const translations = {
    modalTitleCreated: @json(__('logs-modal-title-created')),
    modalTitleUpdated: @json(__('logs-modal-title-updated')),
    modalTitleDeleted: @json(__('logs-modal-title-deleted')),
    modalWas: @json(__('logs-modal-was')),
    modalBecame: @json(__('logs-modal-became'))
};

function showChangesModal(logId, action) {
    const log = logsData.find(l => l.id === logId);
    if (!log) return;

    const modal = document.getElementById('changesModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalContent = document.getElementById('modalContent');

    let html = '';
    let title = '';

    if (action === 'created') {
        title = translations.modalTitleCreated;
        html = '<div class="space-y-3">';
        for (const [key, value] of Object.entries(log.new_values || {})) {
            html += `
                <div class="p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/30">
                    <div class="text-xs text-ink-muted mb-1 font-medium">${key}</div>
                    <div class="text-sm text-ink">${formatValue(value)}</div>
                </div>
            `;
        }
        html += '</div>';
    } else if (action === 'updated') {
        title = translations.modalTitleUpdated;
        html = '<div class="space-y-4">';
        for (const [key, change] of Object.entries(log.changes || {})) {
            html += `
                <div class="p-3 rounded-lg bg-surface border border-line">
                    <div class="text-xs text-ink-muted mb-2 font-medium">${key}</div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-2 rounded bg-red-500/10 border border-red-500/30">
                            <div class="text-xs text-red-300 mb-1">${translations.modalWas}</div>
                            <div class="text-sm text-ink">${formatValue(change.old)}</div>
                        </div>
                        <div class="p-2 rounded bg-emerald-500/10 border border-emerald-500/30">
                            <div class="text-xs text-emerald-300 mb-1">${translations.modalBecame}</div>
                            <div class="text-sm text-ink">${formatValue(change.new)}</div>
                        </div>
                    </div>
                </div>
            `;
        }
        html += '</div>';
    } else if (action === 'deleted') {
        title = translations.modalTitleDeleted;
        html = '<div class="space-y-3">';
        for (const [key, value] of Object.entries(log.old_values || {})) {
            html += `
                <div class="p-3 rounded-lg bg-red-500/10 border border-red-500/30">
                    <div class="text-xs text-ink-muted mb-1 font-medium">${key}</div>
                    <div class="text-sm text-ink">${formatValue(value)}</div>
                </div>
            `;
        }
        html += '</div>';
    }

    modalTitle.textContent = title;
    modalContent.innerHTML = html;
    modal.classList.remove('hidden');
}

function closeChangesModal() {
    document.getElementById('changesModal').classList.add('hidden');
}

function formatValue(value) {
    if (value === null || value === undefined) {
        return '<span class="text-ink-muted">null</span>';
    }
    if (typeof value === 'boolean') {
        return value ? '<span class="text-emerald-300">true</span>' : '<span class="text-red-300">false</span>';
    }
    if (typeof value === 'object') {
        return '<pre class="text-xs">' + JSON.stringify(value, null, 2) + '</pre>';
    }
    return String(value);
}

// Закрытие модального окна по клику вне его
document.getElementById('changesModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeChangesModal();
    }
});
</script>
@endsection

