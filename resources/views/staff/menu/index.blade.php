@extends('layouts.staff')

@php
    $locale = app()->getLocale();
@endphp

@section('content')
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="logo-font text-3xl md:text-4xl mb-2 drop-shadow-[0_0_10px_rgba(255,255,255,0.7)]">
                {{ __('staff-menu-title') }}
            </h1>
            <p class="text-sm text-white/70">
                {{ __('staff-menu-subtitle') }}
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('staff.menu-categories.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-white/60 text-[11px] tracking-[0.18em] uppercase hover:bg-white hover:text-black transition">
                {{-- + {{ __('staff-menu-add-category-button') }} --}}
                <x-icon category="Edit" name="List_Add" class="w-5 h-5"/>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 text-xs text-emerald-300 border border-emerald-500/60 bg-emerald-500/10 px-4 py-2 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div id="menuCategories"
         class="space-y-4"
         data-sort-url="{{ route('staff.menu.sort') }}">

        @forelse($categories as $category)
            @php($catName = $category->name[$locale] ?? ($category->name['ru'] ?? reset($category->name) ?? ''))
            <div class="border border-white/15 rounded-2xl bg-white/5 shadow-lg shadow-black/50 p-4"
                 data-category-id="{{ $category->id }}">
                <div class="flex items-center justify-between gap-3 mb-3 cursor-move handle-category">
                    <div>
                        <h2 class="text-sm font-semibold flex items-center gap-2">
                            <span class="inline-block w-2 h-2 rounded-full bg-white/70"></span>
                            {{ $category->name[$locale] }}

                            @if($category->is_special)
                                <span class="ml-1 px-2 py-0.5 rounded-full border border-purple-400/70
                     text-[10px] uppercase tracking-[0.18em] text-purple-200">
                                    {{ __('staff-menu-category-badge-special') }}
                                </span>
                            @endif
                            @if(!$category->is_available)
                                <span class="ml-1 inline-flex items-center text-amber-400">
                                    <x-icon category="Warning" name="Stop_Sign" class="w-5 h-5"/>
                                    <span class="sr-only">{{ __('staff-menu-category-badge-unavailable') }}</span>
                                </span>
                            @endif
                        </h2>

                        @if($category->description)
                            <p class="text-xs text-white/60 mt-1">
                                {{ $category->description[$locale] }}
                            </p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 text-[11px]">
                        <a href="{{ route('staff.menu-categories.edit', $category) }}"
                           class="px-3 py-1 rounded-full border border-white/40 hover:bg-white hover:text-black transition">
                            {{-- {{ __('staff-menu-category-edit-button') }} --}}
                            <x-icon category="Edit" name="Edit_Pencil_02" class="w-5 h-5"/>
                        </a>

                        <form method="POST" action="{{ route('staff.menu-categories.destroy', $category) }}" id="delete-category-{{ $category->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                    class="px-3 py-1 rounded-full border border-red-400/70 text-red-300 hover:bg-red-500 hover:text-white hover:border-red-500 transition js-delete-category-btn"
                                    data-category-name="{{ $catName }}"
                                    data-form-id="delete-category-{{ $category->id }}">
                                <x-icon category="Interface" name="Trash_Empty" class="w-5 h-5"/>
                                {{-- {{ __('staff-menu-category-delete-button') }} --}}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-[11px] uppercase tracking-[0.18em] text-white/50">
                        {{ __('staff-menu-items-heading') }}
                    </h3>
                    <a href="{{ route('staff.menu-items.create', ['category_id' => $category->id]) }}"
                       class="text-[11px] uppercase tracking-[0.18em] text-white/70 hover:text-white">
                        {{-- + {{ __('staff-menu-items-add-button') }} --}}
                        <x-icon category="Edit" name="Add_Row" class="w-6 h-6"/>
                    </a>
                </div>

                <div class="space-y-2" data-items-container>
                    @foreach($category->items as $item)
                        <div class="flex items-center justify-between gap-3 px-3 py-2 rounded-xl bg-black/40 border border-white/10 cursor-move handle-item"
                             data-item-id="{{ $item->id }}">
                            <div class="flex items-center gap-3">
                                @if($item->image_path)
                                    <img src="{{ asset('storage/'.$item->image_path) }}"
                                         alt="{{ $item->name[$locale] }}"
                                         class="w-10 h-10 rounded-lg object-cover">
                                @endif

                                <div>
                                    <div class="text-xs font-medium flex items-center gap-2">
                                        {{ $item->name[$locale] }}
                                            @if(!$item->is_available)
                                                <span class="inline-flex items-center text-amber-400">
                                                    <x-icon category="Warning" name="Stop_Sign" class="w-5 h-5"/>
                                                    <span class="sr-only">{{ __('staff-menu-item-badge-unavailable') }}</span>
                                                </span>
                                            @endif
                                    </div>
                                    @if($item->description)
                                        <div class="text-[11px] text-white/50 line-clamp-2">
                                            {{ $item->description[$locale] }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <span class="text-sm text-white/80 whitespace-nowrap">
                                    {{ number_format($item->price, 2, '.', ' ') }} €
                                </span>

                                <a href="{{ route('staff.menu-items.edit', $item) }}"
                                   class="text-[11px] uppercase tracking-[0.18em] text-white/70 hover:text-white">
                                    {{-- {{ __('staff-menu-item-edit-button') }} --}}
                                    <x-icon category="Edit" name="Edit_Pencil_Line_01" class="w-5 h-5"/>
                                </a>

                                    <form method="POST" action="{{ route('staff.menu-items.destroy', $item) }}"
                                          class="shrink-0 max-w-full" id="delete-item-{{ $item->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                class="px-3 py-1 rounded-full border border-red-400/70 text-[11px]
                                                       tracking-[0.18em] uppercase text-red-300
                                                       hover:bg-red-500 hover:text-white hover:border-red-500 transition
                                                       whitespace-normal sm:whitespace-nowrap js-delete-item-btn"
                                                data-item-name="{{ $item->name[$locale] ?? ($item->name['ru'] ?? reset($item->name) ?? '') }}"
                                                data-form-id="delete-item-{{ $item->id }}">
                                            <x-icon category="Interface" name="Trash_Full" class="w-5 h-5"/>
                                        </button>
                                    </form>

                            </div>
                        </div>
                    @endforeach
                </div>

                @if($category->items->isEmpty())
                    <p class="text-[11px] text-white/40 mt-2">
                        {{ __('staff-menu-category-empty-items') }}
                    </p>
                @endif
            </div>
        @empty
            <p class="text-sm text-white/60">
                {{ __('staff-menu-empty-categories') }}
            </p>
        @endforelse
    </div>

    <button
        id="backToTop"
        type="button"
        class="hidden fixed bottom-16 md:bottom-8 right-5 md:right-6 z-40 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 text-white p-3 shadow-lg backdrop-blur"
        aria-label="{{ __('staff-menu-back-to-top', [], $locale) }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="w-5 h-5">
            <path d="M12 5l7 7m-7-7L5 12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 5v14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
    </button>

    <div id="deleteCategoryModal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur">
        <div class="max-w-md w-full mx-4 bg-black/95 border border-red-500/50 rounded-2xl shadow-[0_0_40px_rgba(248,113,113,0.5)] p-6">
            <div class="mb-3 flex items-center gap-3">
                <div class="w-9 h-9 rounded-full border border-red-500/70 flex items-center justify-center">
                    <span class="text-red-400 text-lg">!</span>
                </div>
                <h2 class="text-base md:text-lg font-semibold text-white tracking-wide">
                    {{ __('staff-menu-category-delete-modal-title') }}
                </h2>
            </div>

            <p class="text-sm text-white/70 mb-4">
                {{ __('staff-menu-category-delete-modal-text-prefix') }}
                <span id="deleteCategoryName" class="font-semibold text-white"></span>?
            </p>
            <p class="text-xs text-white/40 mb-5">
                {{ __('staff-menu-category-delete-modal-text-note') }}
            </p>

            <div class="flex flex-wrap justify-end gap-3">
                <button type="button"
                        id="deleteCategoryCancelBtn"
                        class="px-5 py-2 rounded-full border border-white/30
                               text-[11px] tracking-[0.22em] uppercase text-white/70
                               hover:text-white hover:border-white/70 hover:bg-white/5 transition">
                    {{ __('staff-menu-category-delete-modal-cancel') }}
                </button>
                <button type="button"
                        id="deleteCategoryConfirmBtn"
                        class="px-5 py-2 rounded-full border border-red-500/80 bg-red-600/90
                               text-[11px] tracking-[0.22em] uppercase text-white
                               shadow-[0_0_14px_rgba(248,113,113,0.7)]
                               hover:bg-red-500 transition">
                    {{ __('staff-menu-category-delete-modal-confirm') }}
                </button>
            </div>
        </div>
    </div>

    <div id="deleteItemModal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur">
        <div class="max-w-md w-full mx-4 bg-black/95 border border-red-500/50 rounded-2xl shadow-[0_0_40px_rgba(248,113,113,0.5)] p-6">
            <div class="mb-3 flex items-center gap-3">
                <div class="w-9 h-9 rounded-full border border-red-500/70 flex items-center justify-center">
                    <span class="text-red-400 text-lg">!</span>
                </div>
                <h2 class="text-base md:text-lg font-semibold text-white tracking-wide">
                    {{ __('staff-menu-item-delete-modal-title') }}
                </h2>
            </div>

            <p class="text-sm text-white/70 mb-4">
                {{ __('staff-menu-item-delete-modal-text-prefix') }}
                <span id="deleteItemName" class="font-semibold text-white"></span>?
            </p>
            <p class="text-xs text-white/40 mb-5">
                {{ __('staff-menu-item-delete-modal-text-note') }}
            </p>

            <div class="flex flex-wrap justify-end gap-3">
                <button type="button"
                        id="deleteItemCancelBtn"
                        class="px-5 py-2 rounded-full border border-white/30
                               text-[11px] tracking-[0.22em] uppercase text-white/70
                               hover:text-white hover:border-white/70 hover:bg-white/5 transition">
                    {{ __('staff-menu-item-delete-modal-cancel') }}
                </button>
                <button type="button"
                        id="deleteItemConfirmBtn"
                        class="px-5 py-2 rounded-full border border-red-500/80 bg-red-600/90
                               text-[11px] tracking-[0.22em] uppercase text-white
                               shadow-[0_0_14px_rgba(248,113,113,0.7)]
                               hover:bg-red-500 transition">
                    {{ __('staff-menu-item-delete-modal-confirm') }}
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const menuContainer = document.getElementById('menuCategories');
            if (!menuContainer) return;

            const sortUrl = menuContainer.dataset.sortUrl;

            const categorySortable = new Sortable(menuContainer, {
                handle: '.handle-category',
                animation: 150,
                onEnd: saveOrder
            });

            const itemContainers = menuContainer.querySelectorAll('[data-items-container]');
            itemContainers.forEach(container => {
                new Sortable(container, {
                    handle: '.handle-item',
                    group: 'menu-items',
                    animation: 150,
                    onEnd: saveOrder
                });
            });

            function collectData() {
                const categories = [];
                menuContainer.querySelectorAll('[data-category-id]').forEach((catEl) => {
                    const categoryId = catEl.getAttribute('data-category-id');
                    const items = [];
                    catEl.querySelectorAll('[data-items-container] [data-item-id]').forEach(itemEl => {
                        items.push(itemEl.getAttribute('data-item-id'));
                    });
                    categories.push({
                        id: parseInt(categoryId, 10),
                        items: items.map(i => parseInt(i, 10))
                    });
                });
                return {categories};
            }

            let saveTimeout = null;

            function saveOrder() {
                if (saveTimeout) {
                    clearTimeout(saveTimeout);
                }

                saveTimeout = setTimeout(() => {
                    const payload = collectData();

                    fetch(sortUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    })
                        .then(r => r.json())
                        .then(data => {
                        })
                        .catch(err => {
                            console.error(err);
                            alert(@json(__('staff-menu-sort-error')));
                        });
                }, 300);
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('deleteCategoryModal');
            const nameSpan = document.getElementById('deleteCategoryName');
            const cancelBtn = document.getElementById('deleteCategoryCancelBtn');
            const confirmBtn = document.getElementById('deleteCategoryConfirmBtn');

            let currentFormId = null;

            const openModal = (catName, formId) => {
                nameSpan.textContent = catName || '';
                currentFormId = formId;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                currentFormId = null;
            };

            document.querySelectorAll('.js-delete-category-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const catName = btn.getAttribute('data-category-name');
                    const formId = btn.getAttribute('data-form-id');
                    openModal(catName, formId);
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

        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('deleteItemModal');
            const nameSpan = document.getElementById('deleteItemName');
            const cancelBtn = document.getElementById('deleteItemCancelBtn');
            const confirmBtn = document.getElementById('deleteItemConfirmBtn');

            let currentFormId = null;

            const openModal = (itemName, formId) => {
                nameSpan.textContent = itemName || '';
                currentFormId = formId;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                currentFormId = null;
            };

            document.querySelectorAll('.js-delete-item-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const itemName = btn.getAttribute('data-item-name');
                    const formId = btn.getAttribute('data-form-id');
                    openModal(itemName, formId);
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

        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('backToTop');
            if (!btn) return;

            const toggle = () => {
                const show = window.scrollY > 200;
                btn.classList.toggle('hidden', !show);
            };

            toggle();
            window.addEventListener('scroll', toggle);

            btn.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    </script>
@endpush
