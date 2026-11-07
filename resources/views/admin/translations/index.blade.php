@extends('layouts.admin')

@section('content')
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="logo-font text-2xl md:text-3xl lg:text-4xl mb-2">
                {{ __('admin-translations-title') }}
            </h1>
            <p class="text-xs md:text-sm text-ink-muted">
                {{ __('admin-translations-subtitle') }}
            </p>
        </div>

        <div class="flex flex-1 md:flex-none items-stretch md:items-center gap-3 flex-col md:flex-row">
            <form method="GET" action="{{ route('admin.translations.index') }}"
                  class="flex items-center gap-2 w-full md:w-auto">
                <input type="text"
                       name="q"
                       value="{{ $search }}"
                       placeholder="{{ __('admin-translations-search-placeholder') }}"
                       class="bg-ground/60 border border-line rounded-full px-3 py-1.5 text-xs text-ink w-full md:w-56">
            </form>

            <a href="{{ route('admin.translations.create') }}"
               class="inline-flex justify-center items-center gap-2 px-4 py-2 rounded-full border border-red-500/70
                      bg-nebula text-xs md:text-xs text-on-nebula w-full md:w-auto
                      hover:bg-ink transition">
                <x-icon category="Edit" name="Add_Plus_Circle" class="w-5 h-5 shrink-0"/>
                <span>{{ __('admin-translations-create-button') }}</span>
            </a>

        </div>
    </div>

    @if (session('status'))
        <div class="mb-6 bg-emerald-950/60 border border-emerald-500/70 text-emerald-100 text-xs md:text-sm rounded-xl px-4 py-3">
            <p class="font-semibold mb-1 text-xs tracking-wide uppercase text-emerald-200">
                {{ __('admin-translations-alert-success-title') }}
            </p>
            <p>{{ session('status') }}</p>
        </div>
    @endif

    <div id="translations-list">
        @include('admin.translations.partials.list', [
            'translations' => $translations,
            'locales' => $locales,
            'default' => $default
        ])
    </div>

    <div id="deleteTranslationModal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-ground/70 backdrop-blur">
        <div class="max-w-md w-full mx-4 bg-ground/95 border border-red-500/50 rounded-2xl p-6">
            <div class="mb-3 flex items-center gap-3">
                <div class="w-9 h-9 rounded-full border border-red-500/70 flex items-center justify-center">
                    <span class="text-red-400 text-lg">!</span>
                </div>
                <h2 class="text-base md:text-lg font-semibold text-ink tracking-wide">
                    {{ __('admin-translations-delete-modal-title') }}
                </h2>
            </div>

            <p class="text-sm text-ink-muted mb-4">
                {{ __('admin-translations-delete-modal-text-prefix') }}
                <span id="deleteTranslationKey" class="font-semibold text-ink break-all"></span>?
            </p>
            <p class="text-xs text-ink-muted mb-5">
                {{ __('admin-translations-delete-modal-text-note') }}
            </p>

            <div class="flex flex-wrap justify-end gap-3">
                <button type="button"
                        id="deleteTranslationCancelBtn"
                        class="px-5 py-2 rounded-full border border-line-strong
                               text-xs text-ink-muted
                               hover:text-ink hover:border-line-strong hover:bg-surface transition">
                    {{ __('admin-translations-delete-modal-cancel') }}
                </button>
                <button type="button"
                        id="deleteTranslationConfirmBtn"
                        class="px-5 py-2 rounded-full border border-red-500/80 bg-red-600/90
                               text-xs text-ink
                               hover:bg-red-500 transition">
                    {{ __('admin-translations-delete-modal-confirm') }}
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.querySelector('input[name="q"]');
            const listContainer = document.getElementById('translations-list');
            const modal = document.getElementById('deleteTranslationModal');
            const keySpan = document.getElementById('deleteTranslationKey');
            const cancelBtn = document.getElementById('deleteTranslationCancelBtn');
            const confirmBtn = document.getElementById('deleteTranslationConfirmBtn');
            if (!input || !listContainer) return;

            let timer = null;
            let currentFormId = null;

            const openModal = (key, formId) => {
                keySpan.textContent = key || '';
                currentFormId = formId;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                currentFormId = null;
            };

            const bindDeleteButtons = () => {
                listContainer.querySelectorAll('.js-delete-translation-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const key = btn.getAttribute('data-key');
                        const formId = btn.getAttribute('data-form-id');
                        openModal(key, formId);
                    });
                });
            };

            bindDeleteButtons();

            function fetchList(url) {
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                .then(r => r.text())
                .then(html => {
                    listContainer.innerHTML = html;
                    bindDeleteButtons();
                })
                .catch(console.error);
            }

            function performSearch() {
                const q = input.value || '';
                const url = new URL(@json(route('admin.translations.index')), window.location.origin);

                if (q.length > 0) url.searchParams.set('q', q);
                else url.searchParams.delete('q');

                fetchList(url.toString());
            }

            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(performSearch, 250);
            });

            // Клики по пагинации — тоже через fetch (без перезагрузки)
            listContainer.addEventListener('click', (e) => {
                const a = e.target.closest('a');
                if (!a) return;

                const nav = a.closest('nav[aria-label="Pagination Navigation"]');
                if (!nav) return;

                e.preventDefault();
                fetchList(a.href);
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
