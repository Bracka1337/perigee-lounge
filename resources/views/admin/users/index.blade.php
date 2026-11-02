@extends('layouts.admin')

@section('content')
    @php
        $currentFilter = $currentFilter ?? ($role ?? 'all');
    @endphp

    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="logo-font text-2xl md:text-3xl lg:text-4xl mb-2">
                {{ __('admin-users-title') }}
            </h1>
            <p class="text-xs md:text-sm text-ink-muted">
                {{ __('admin-users-subtitle') }}
            </p>
        </div>

        @if(Route::has('admin.users.create'))
<a href="{{ route('admin.users.create') }}"
   class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full border border-red-500/70
          bg-nebula text-xs md:text-xs text-on-nebula
          hover:bg-ink transition"
   aria-label="{{ __('admin-users-create-button') }}">
    <x-icon category="Edit" name="Add_Plus_Circle" class="w-5 h-5"/>
    <span>{{ __('admin-users-create-button') }}</span>
</a>

        @endif
    </div>

    @if (session('status'))
        <div class="mb-6 bg-emerald-950/60 border border-emerald-500/70 text-emerald-100 text-xs md:text-sm rounded-xl px-4 py-3">
            <p class="font-semibold mb-1 text-xs tracking-wide uppercase text-emerald-200">
                {{ __('admin-users-alert-success-title') }}
            </p>
            <p>{{ session('status') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 bg-red-950/60 border border-red-500/70 text-red-100 text-xs md:text-sm rounded-xl px-4 py-3">
            <p class="font-semibold mb-1 text-xs tracking-wide uppercase text-red-200">
                {{ __('admin-users-alert-error-title') }}
            </p>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="mb-6 flex flex-wrap gap-2 md:gap-3">
        @php
            $filters = [
                'all'   => __('admin-users-filter-all'),
                'admin' => __('admin-users-filter-admin'),
                'staff' => __('admin-users-filter-staff'),
                'client'=> __('admin-users-filter-client'),
            ];
        @endphp

        @foreach($filters as $value => $label)
            <a href="{{ route('admin.users.index', array_merge(request()->except('page'), ['role' => $value])) }}"
               class="px-3 md:px-4 py-1.5 rounded-full border text-xs md:text-xs md: transition
                      @if($currentFilter === $value)
                          bg-nebula border-nebula text-on-nebula
                      @else
                          bg-surface border-line text-ink-muted hover:bg-line hover:text-ink
                      @endif">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="space-y-3">
        <div class="md:hidden space-y-3">
            @forelse($users as $user)
                @php
                    if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                        $roleLabelKey = 'admin-users-role-label-admin';
                        $roleColor = 'bg-nebula border-nebula text-red-50';
                    } elseif (method_exists($user, 'isWorker') && $user->isWorker()) {
                        $roleLabelKey = 'admin-users-role-label-staff';
                        $roleColor = 'bg-emerald-600/70 border-emerald-400 text-emerald-50';
                    } else {
                        $roleLabelKey = 'admin-users-role-label-client';
                        $roleColor = 'bg-slate-700/80 border-slate-400 text-slate-50';
                    }
                @endphp

                <div class="rounded-2xl bg-ground/40 border border-line px-4 py-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="font-medium text-ink text-sm">
                                {{ $user->name ?? '—' }}
                            </div>
                            <div class="text-xs text-ink-muted break-all">
                                {{ $user->email }}
                            </div>
                            @if(!empty($user->phone))
                                <div class="text-xs text-ink-muted mt-0.5">
                                    {{ $user->phone }}
                                </div>
                            @endif
                        </div>

                        <span class="inline-flex items-center px-3 py-1 rounded-full border text-xs {{ $roleColor }}">
                            {{ __($roleLabelKey) }}
                        </span>
                    </div>

                    <div class="mt-3 text-xs text-ink-muted flex flex-wrap items-center gap-2">
                        @if($user->created_at)
                            <span class="inline-flex items-center gap-1">
                                <span class="text-ink-muted">{{ __('admin-users-created-at-label') }}</span>
                                <span>{{ $user->created_at->format('d.m.Y') }}</span>
                                <span class="text-ink-muted">/</span>
                                <span>{{ $user->created_at->format('H:i') }}</span>
                            </span>
                        @else
                            <span class="text-ink-muted">{{ __('admin-users-created-at-empty') }}</span>
                        @endif
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @if(Route::has('admin.users.show') && method_exists($user, 'isClient') && $user->isClient())
                            <a href="{{ route('admin.users.show', $user) }}"
                               class="flex-1 min-w-[120px] inline-flex justify-center items-center gap-2 px-3 py-1.5 rounded-full
                                      text-xs text-ink-soft hover:text-ink
                                      border border-line hover:border-line-strong hover:bg-surface transition">
                                <x-icon category="Interface" name="External_Link" class="w-5 h-5"/>
                            </a>
                        @endif

                        @if(Route::has('admin.users.edit'))
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="flex-1 min-w-[120px] inline-flex justify-center items-center gap-2 px-3 py-1.5 rounded-full
                                      text-xs text-ink-soft hover:text-ink
                                      border border-line hover:border-line-strong hover:bg-surface transition">
                                <x-icon category="Edit" name="Edit_Pencil_02" class="w-5 h-5"/>
                                {{-- {{ __('admin-users-edit-button') }} --}}
                            </a>
                        @endif

                        @if(Route::has('admin.users.destroy'))
                            <form id="delete-user-{{ $user->id }}"
                                  action="{{ route('admin.users.destroy', $user) }}"
                                  method="POST"
                                  class="flex-1 min-w-[120px] inline">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                        class="w-full inline-flex justify-center items-center gap-2 px-3 py-1.5 rounded-full
                                               text-xs text-red-300 hover:text-red-100
                                               border border-red-500/70 hover:border-red-400 hover:bg-red-500/20
                                               transition js-delete-user-btn"
                                        data-user-name="{{ $user->name ?? $user->email }}"
                                        data-user-email="{{ $user->email }}"
                                        data-form-id="delete-user-{{ $user->id }}">
                                    <x-icon category="Interface" name="Trash_Empty" class="w-5 h-5"/>
                                    {{--{{ __('admin-users-delete-button) }} --}}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-ground/40 border border-line px-4 py-6 text-center text-ink-muted text-sm">
                    {{ __('admin-users-empty') }}
                </div>
            @endforelse
        </div>

        <div class="hidden md:block space-y-3">
            @forelse($users as $user)
                @php
                    if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                        $roleLabelKey = 'admin-users-role-label-admin';
                        $roleColor = 'bg-nebula border-nebula text-red-50';
                    } elseif (method_exists($user, 'isWorker') && $user->isWorker()) {
                        $roleLabelKey = 'admin-users-role-label-staff';
                        $roleColor = 'bg-emerald-600/70 border-emerald-400 text-emerald-50';
                    } else {
                        $roleLabelKey = 'admin-users-role-label-client';
                        $roleColor = 'bg-slate-700/80 border-slate-400 text-slate-50';
                    }
                @endphp

                <div class="rounded-2xl bg-ground/40 border border-line px-5 py-4 hover:bg-surface transition">
                    <div class="flex items-start justify-between gap-6">
                        <div class="min-w-0">
                            <div class="flex items-center gap-3">
                                <div class="font-semibold text-ink truncate">
                                    {{ $user->name ?? '—' }}
                                </div>

                                <span class="inline-flex items-center px-3 py-1 rounded-full border text-xs {{ $roleColor }}">
                                    {{ __($roleLabelKey) }}
                                </span>
                            </div>

                            <div class="mt-1 text-xs text-ink-muted break-all">
                                {{ $user->email }}
                            </div>

                            @if(!empty($user->phone))
                                <div class="mt-1 text-xs text-ink-muted">
                                    {{ $user->phone }}
                                </div>
                            @endif

                            <div class="mt-3 text-xs text-ink-muted">
                                <span class="text-ink-muted">{{ __('admin-users-created-at-label') }}</span>
                                @if($user->created_at)
                                    <span class="text-ink-muted ml-1">{{ $user->created_at->format('d.m.Y') }}</span>
                                    <span class="text-ink-muted">/</span>
                                    <span class="text-ink-muted">{{ $user->created_at->format('H:i') }}</span>
                                @else
                                    <span class="text-ink-muted ml-1">—</span>
                                @endif
                            </div>
                        </div>

                        <div class="shrink-0 flex items-center gap-2">
                            @if(Route::has('admin.users.show') && method_exists($user, 'isClient') && $user->isClient())
                                <a href="{{ route('admin.users.show', $user) }}"
                                   class="h-9 inline-flex items-center justify-center gap-2 px-4 rounded-full border border-line
                                          text-xs text-ink-soft hover:text-ink
                                          hover:border-line-strong hover:bg-surface transition">
                                    <x-icon category="Interface" name="External_Link" class="w-5 h-5"/>
                                </a>
                            @endif

                            @if(Route::has('admin.users.edit'))
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="h-9 inline-flex items-center justify-center gap-2 px-4 rounded-full border border-line
                                          text-xs text-ink-soft hover:text-ink
                                          hover:border-line-strong hover:bg-surface transition">
                                    <x-icon category="Edit" name="Edit_Pencil_02" class="w-5 h-5"/>
                                    {{-- {{ __('admin-users-edit-button') }} --}}
                                </a>
                            @endif

                            @if(Route::has('admin.users.destroy'))
                                <form id="delete-user-{{ $user->id }}-desktop"
                                      action="{{ route('admin.users.destroy', $user) }}"
                                      method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            class="h-9 inline-flex items-center justify-center gap-2 px-4 rounded-full border border-red-500/70
                                                   text-xs text-red-300 hover:text-red-100
                                                   hover:border-red-400 hover:bg-red-500/20 transition js-delete-user-btn"
                                            data-user-name="{{ $user->name ?? $user->email }}"
                                            data-user-email="{{ $user->email }}"
                                            data-form-id="delete-user-{{ $user->id }}-desktop">
                                        <x-icon category="Interface" name="Trash_Empty" class="w-5 h-5"/>
                                        {{-- {{ __('admin-users-delete-button') }} --}}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-ground/40 border border-line px-5 py-10 text-center text-ink-muted">
                    {{ __('admin-users-empty') }}
                </div>
            @endforelse
        </div>

        @if(($users instanceof \Illuminate\Pagination\LengthAwarePaginator || $users instanceof \Illuminate\Pagination\Paginator) && $users->hasPages())
            @php
                $p = $users->appends(request()->query());

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
    </div>

    <div id="deleteUserModal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-ground/70 backdrop-blur">
        <div class="max-w-md w-full mx-4 bg-ground/95 border border-red-500/50 rounded-2xl p-6">
            <div class="mb-3 flex items-center gap-3">
                <div class="w-9 h-9 rounded-full border border-red-500/70 flex items-center justify-center">
                    <span class="text-red-400 text-lg">!</span>
                </div>
                <h2 class="text-base md:text-lg font-semibold text-ink tracking-wide">
                    {{ __('admin-users-delete-modal-title') }}
                </h2>
            </div>

            <p class="text-sm text-ink-muted mb-4">
                {{ __('admin-users-delete-modal-text-prefix') }}
                <span id="deleteUserName" class="font-semibold text-ink"></span>?
            </p>
            <p class="text-xs text-ink-muted mb-5">
                {{ __('admin-users-delete-modal-text-note') }}
            </p>

            <div class="flex flex-wrap justify-end gap-3">
                <button type="button"
                        id="deleteUserCancelBtn"
                        class="px-5 py-2 rounded-full border border-line-strong
                               text-xs text-ink-muted
                               hover:text-ink hover:border-line-strong hover:bg-surface transition">
                    {{ __('admin-users-delete-modal-cancel') }}
                </button>
                <button type="button"
                        id="deleteUserConfirmBtn"
                        class="px-5 py-2 rounded-full border border-red-500/80 bg-red-600/90
                               text-xs text-ink
                               hover:bg-red-500 transition">
                    {{ __('admin-users-delete-modal-confirm') }}
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('deleteUserModal');
            const nameSpan = document.getElementById('deleteUserName');
            const cancelBtn = document.getElementById('deleteUserCancelBtn');
            const confirmBtn = document.getElementById('deleteUserConfirmBtn');

            let currentFormId = null;

            const openModal = (userName, formId) => {
                nameSpan.textContent = userName || '';
                currentFormId = formId;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                currentFormId = null;
            };

            document.querySelectorAll('.js-delete-user-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const userName = btn.getAttribute('data-user-name');
                    const formId = btn.getAttribute('data-form-id');
                    openModal(userName, formId);
                });
            });

            cancelBtn.addEventListener('click', closeModal);

            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });

            confirmBtn.addEventListener('click', () => {
                if (!currentFormId) return;
                const form = document.getElementById(currentFormId);
                if (form) form.submit();
            });
        });
    </script>
@endpush
