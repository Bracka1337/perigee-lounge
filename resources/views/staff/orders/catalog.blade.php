@extends('layouts.staff')

@php($locale = app()->getLocale())

@section('content')
<div class="mb-4">
    <a href="{{ route('staff.orders.show', ['locale' => $locale, 'order' => $order]) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 text-sm text-white/80 transition">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="w-4 h-4">
            <path d="M19 12H5m5-7l-7 7 7 7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span>{{ __('common-back-to-order') }}</span>
    </a>
</div>

<div class="mb-6">
    <label class="w-full inline-flex items-center gap-3 px-4 py-3 rounded-xl bg-white/5 border border-white/10 shadow-inner shadow-black/20">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="w-5 h-5 text-white/60">
            <path d="M11 4a7 7 0 1 1 0 14 7 7 0 0 1 0-14Zm0 0L5 5m13.5 14L16 16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <input
            id="itemsSearch"
            type="search"
            placeholder="{{ __('common-search-items') }}"
            class="w-full bg-transparent outline-none text-sm text-white placeholder:text-white/50"
            autocomplete="off"
            spellcheck="false"
        >
    </label>
</div>

<div class="flex flex-col md:flex-row gap-6">
    {{-- Категории: mobile pills --}}
    <div class="md:hidden">
        <div class="flex gap-2 overflow-x-auto pb-2 hide-scrollbar" id="categoryPills">
            @foreach ($categories as $category)
                @php($catName = $category->name[$locale] ?? ($category->name['ru'] ?? reset($category->name) ?? ''))
                <button type="button"
                        class="category-pill px-3 py-2 rounded-full border border-white/15 bg-white/5 text-xs text-white/80 whitespace-nowrap"
                        data-category="cat-{{ $category->id }}">
                    {{ $catName }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Категории: desktop sidebar --}}
    <aside class="hidden md:block md:w-1/4 bg-white/5 border border-white/10 rounded-2xl p-4">
        <h2 class="text-xs uppercase tracking-[0.2em] text-white/60 mb-3">{{ __('common-categories') }}</h2>
        <ul class="space-y-2 text-sm text-white/80" id="categoryList">
            @foreach ($categories as $category)
                @php($catName = $category->name[$locale] ?? ($category->name['ru'] ?? reset($category->name) ?? ''))
                <li>
                    <button type="button"
                            class="category-pill w-full text-left px-3 py-2 rounded-lg border border-transparent hover:border-white/15 hover:bg-white/5 transition"
                            data-category="cat-{{ $category->id }}">
                        <span class="font-semibold">{{ $catName }}</span>
                    </button>
                </li>
            @endforeach
        </ul>
    </aside>

    {{-- Товары --}}
    <section class="md:w-3/4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-4 gap-y-3 self-start content-start" id="itemsGrid">
        @foreach ($categories as $category)
            @php($catName = $category->name[$locale] ?? ($category->name['ru'] ?? reset($category->name) ?? ''))

            <div id="cat-{{ $category->id }}" class="col-span-full pt-2 pb-1" data-category-header="cat-{{ $category->id }}">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-white">{{ $catName }}</span>
                    <span class="h-px flex-1 bg-white/10"></span>
                </div>
            </div>

            @foreach ($category->items as $item)
                @php($itemName = $item->name[$locale] ?? ($item->name['ru'] ?? reset($item->name) ?? ''))
                @php($itemNameLower = mb_strtolower($itemName))
                <div class="product-card rounded-2xl border border-white/10 bg-white/5 p-3 flex flex-col gap-1" data-category="cat-{{ $category->id }}" data-name="{{ $itemNameLower }}">
                    @if ($item->image_url)
                        <img
                            src="{{ $item->image_url }}"
                            alt="{{ $itemName }}"
                            class="w-full h-32 object-cover rounded-xl bg-black/40"
                            loading="lazy"
                        >
                    @endif

                    <div class="font-medium text-white truncate product-name" title="{{ $itemName }}">
                        {{ $itemName }}
                    </div>
                    <div class="text-xs text-white/60">{{ number_format($item->price, 2, '.', ' ') }} €</div>

                    <form method="POST" action="{{ route('staff.orders.items.store', ['locale' => $locale, 'order' => $order]) }}" class="pt-0">
                        @csrf
                        <input type="hidden" name="menu_item_id" value="{{ $item->id }}">
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/30 border border-emerald-500/40 text-sm text-emerald-100 transition">
                            <x-icon category="Shopping" name="Shopping_Cart_01" class="w-4 h-4"/>
                            <span>{{ __('common-add') }}</span>
                        </button>
                    </form>
                </div>
            @endforeach
        @endforeach
    </section>
</div>

<button
    id="backToTop"
    type="button"
    class="hidden fixed bottom-6 right-6 z-40 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 text-white p-3 shadow-lg backdrop-blur"
    aria-label="{{ __('common-back-to-top') }}"
>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="w-5 h-5">
        <path d="M12 5l7 7m-7-7L5 12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M12 5v14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
    </svg>
</button>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const pills = Array.from(document.querySelectorAll('.category-pill'));
        const setActive = (cat) => {
            pills.forEach(p => {
                const active = p.dataset.category === cat;
                p.classList.toggle('border-white/40', active);
                p.classList.toggle('bg-white/15', active);
                p.classList.toggle('text-white', active);
            });
        };

        pills.forEach(p => {
            p.addEventListener('click', () => {
                const id = p.dataset.category;
                const target = document.getElementById(id);
                if (target) {
                    const offset = 80;
                    const targetTop = target.getBoundingClientRect().top + window.scrollY - offset;
                    window.scrollTo({ top: targetTop, behavior: 'smooth' });
                }
                setActive(id);
            });
        });

        if (pills.length) {
            setActive(pills[0].dataset.category);
        }

        const searchInput = document.getElementById('itemsSearch');
        const productCards = Array.from(document.querySelectorAll('.product-card'));
        const categoryHeaders = Array.from(document.querySelectorAll('[data-category-header]'));

        const applySearch = () => {
            const query = (searchInput?.value || '').trim().toLowerCase();
            const visibleByCategory = {};

            productCards.forEach(card => {
                const name = (card.dataset.name || card.querySelector('.product-name')?.textContent || '').toLowerCase();
                const matches = !query || name.includes(query);
                card.classList.toggle('hidden', !matches);
                if (matches) {
                    visibleByCategory[card.dataset.category] = true;
                }
            });

            categoryHeaders.forEach(header => {
                const catId = header.dataset.categoryHeader;
                const showHeader = !query || !!visibleByCategory[catId];
                header.classList.toggle('hidden', !showHeader);
            });
        };

        if (searchInput) {
            searchInput.addEventListener('input', applySearch);
        }

        const backToTop = document.getElementById('backToTop');
        const toggleBackToTop = () => {
            if (!backToTop) return;
            const show = window.scrollY > 200;
            backToTop.classList.toggle('hidden', !show);
        };

        if (backToTop) {
            toggleBackToTop();
            window.addEventListener('scroll', toggleBackToTop);
            backToTop.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
    });
</script>
@endpush
@endsection
