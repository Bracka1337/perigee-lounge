@extends('layouts.app')

@section('seo_key', 'menu')


@section('content')
    <div class="w-full min-h-screen bg-gradient-to-b from-black via-[#050816] to-black text-white pt-24 pb-16 overflow-x-hidden">

        <div class="max-w-6xl mx-auto px-6 scroll-mt-32 md:scroll-mt-40" id="menu-top">
            @if(session('success'))
                <div class="mb-4 text-xs text-emerald-300 border border-emerald-500/60 bg-emerald-500/10 px-4 py-2 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 text-xs text-red-300 border border-red-500/60 bg-red-500/10 px-4 py-2 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="relative mb-12 text-center">
                <div class="absolute inset-x-0 -top-10 h-32 bg-gradient-to-b from-white/15 via-white/5 to-transparent blur-3xl opacity-50"></div>

                <div class="relative inline-flex flex-col items-center gap-1 mb-4">
                    <p class="text-[10px] tracking-[0.35em] uppercase text-white/60">
                        {{ __('perigee-rest-name') }}
                    </p>
                    <h1 class="logo-font text-4xl md:text-5xl drop-shadow-[0_0_14px_rgba(255,255,255,0.9)]">
                        {{ __('perigee-name') }}
                    </h1>
                </div>

                <p class="text-xs uppercase tracking-[0.25em] text-white/50 mb-2">
                    {{ __('header-menu') }}
                </p>

                <p class="text-sm md:text-base text-white/70 max-w-2xl mx-auto">
                    {{ __('menu-title') }}
                </p>
            </div>

            @isset($qrTable)
                <div class="mb-12">
                    <a href="{{ route('client.table.cart.show', ['locale' => app()->getLocale(), 'code' => $qrCode]) }}"
                       class="group flex items-center justify-between gap-4 p-4 rounded-2xl border border-white/40 bg-white/5 hover:border-white/60 hover:bg-white/10 transition">
                        <div>
                            <h3 class="text-white font-semibold">
                                {{ __('qr-table-order-title') ?? 'Ваш заказ на стол' }}
                            </h3>
                            <p class="text-sm text-white/60 mt-1">
                                {{ __('qr-table-name-label') ?? 'Стол' }}: {{ $qrTable->name }}
                            </p>
                        </div>
                        <img src="{{ asset('icons/coolicons/Interface/Shopping_Cart_01.svg') }}" alt="" class="w-5 h-5 flex-shrink-0 invert opacity-80 group-hover:translate-x-1 transition-transform">
                    </a>
                </div>
            @endisset

            @if($categories->isNotEmpty())
                <div class="mb-10">
                    <div class="flex flex-wrap justify-center gap-3 text-[11px] uppercase tracking-[0.22em]">
                        <a href="#menu-top"
                           class="px-5 py-2 rounded-full border border-white/80 bg-white/10 text-white shadow-[0_0_14px_rgba(255,255,255,0.8)]
                                  hover:bg-white/20 transition">
                            {{ __('menu-button-all-product') }}
                        </a>

                        @foreach($categories as $category)
                            @php
                                $isSpecial = $category->is_special ?? false;
                                $catName = method_exists($category, 't')
                                    ? $category->t('name')
                                    : ($category->name[config('site.default_locale')] ?? '');
                            @endphp

                            <a href="#category-{{ $category->id }}"
                               class="px-5 py-2 rounded-full border text-white/70 transition
                                      @if($isSpecial)
                                          border-purple-400/80 text-purple-200/90 bg-purple-500/15
                                          hover:bg-purple-400/25 hover:border-purple-300 hover:text-white
                                      @else
                                          border-white/20 bg-white/5
                                          hover:text-white hover:border-white/70 hover:bg-white/10
                                      @endif">
                                {{ $catName }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @forelse($categories as $category)
                @php
                    $isSpecial = $category->is_special ?? false;
                    $catName = method_exists($category, 't')
                        ? $category->t('name')
                        : ($category->name[config('site.default_locale')] ?? '');
                    $catDesc = method_exists($category, 't')
                        ? $category->t('description')
                        : ($category->description[config('site.default_locale')] ?? null);

                    $catAvailable = $category->is_available ?? true;
                @endphp

                <section class="mb-14 scroll-mt-32 md:scroll-mt-40 {{ !$catAvailable ? 'opacity-80' : '' }}" id="category-{{ $category->id }}">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-5">
<div class="flex flex-wrap items-center gap-2">
    <h2 class="text-xl md:text-2xl font-semibold leading-none {{ $isSpecial ? 'text-purple-200' : 'text-white' }}">
        {{ $catName }}
    </h2>

    @if(!$catAvailable)
        <span class="inline-flex items-center justify-center h-6 px-3 rounded-full self-center
                     text-[10px] uppercase tracking-[0.22em] leading-none
                     border border-amber-400/70 bg-amber-500/15 text-amber-200">
            {{ __('menu-unavailable') }}
        </span>
    @endif

    @if($isSpecial)
        <span class="inline-flex items-center justify-center h-6 px-3 rounded-full self-center
                     text-[10px] uppercase tracking-[0.22em] leading-none
                     border border-purple-400/70 bg-purple-600/20 text-purple-300">
            {{ __('menu-special-category') }}
        </span>
    @endif
</div>


                        @if($catDesc)
                            <p class="text-[11px] text-white/45 uppercase tracking-[0.22em] md:text-right">
                                {{ $catDesc }}
                            </p>
                        @endif
                    </div>

                    @if($category->items->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                            @foreach($category->items as $item)
                                @php
                                    $itemName = method_exists($item, 't')
                                        ? $item->t('name')
                                        : ($item->name[config('site.default_locale')] ?? '');

                                    $itemDesc = method_exists($item, 't')
                                        ? $item->t('description')
                                        : ($item->description[config('site.default_locale')] ?? null);

                                    if (method_exists($item, 't')) {
                                        $itemAllergens = $item->t('allergens');
                                    } else {
                                        $itemAllergens = $item->allergens[$locale]
                                            ?? ($item->allergens[$defaultLocale] ?? null);
                                    }

                                    $itemAvailable = $item->is_available ?? true;
                                    $effectiveAvailable = $catAvailable && $itemAvailable;
                                @endphp

                                <article
                                    class="group rounded-3xl overflow-hidden border shadow-[0_18px_45px_rgba(0,0,0,0.9)] backdrop-blur
                                        @if($isSpecial)
                                            border-purple-400/70 bg-gradient-to-b from-purple-500/20 via-black/80 to-black
                                        @else
                                            border-white/10 bg-gradient-to-b from-white/5 via-black/80 to-black
                                        @endif
                                        {{ !$effectiveAvailable ? 'opacity-75 border-amber-400/60' : 'js-open-menu-card cursor-pointer' }}
                                    "
                                    @if($effectiveAvailable)
                                        role="button" tabindex="0"
                                        data-name="{{ e($itemName) }}"
                                        data-desc="{{ e($itemDesc ?? '') }}"
                                        data-price="{{ number_format($item->price, 2, '.', ' ') }} €"
                                        data-image="{{ $item->image_path ? asset('storage/'.$item->image_path) : '' }}"
                                        data-category="{{ e($catName) }}"
                                        data-allergens="{{ e($itemAllergens ?? '') }}"
                                    @endif
                                >
                                    @if($item->image_path)
                                        <div class="relative overflow-hidden">
                                            <img src="{{ asset('storage/'.$item->image_path) }}"
                                                 alt="{{ $itemName }}"
                                                 class="w-full h-40 object-cover transition duration-500 group-hover:scale-105 group-hover:brightness-110">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
                                        </div>
                                    @endif

                                    <div class="p-5 flex flex-col h-full">
                                        <div class="flex justify-between gap-4 mb-2">
                                            <div class="flex flex-col gap-1">
                                                <h3 class="text-sm md:text-base font-medium">
                                                    {{ $itemName }}
                                                </h3>
                                            </div>

                                            <span class="text-sm md:text-base whitespace-nowrap
                                                @if($isSpecial)
                                                    text-purple-100
                                                @else
                                                    {{ $effectiveAvailable ? 'text-white/90' : 'text-white/40 line-through' }}
                                                @endif
                                            ">
                                                {{ number_format($item->price, 2, '.', ' ') }} €
                                            </span>
                                        </div>

                                        @if($itemDesc)
                                            <p class="text-xs md:text-sm text-white/65 leading-relaxed">
                                                {{ $itemDesc }}
                                            </p>
                                        @endif

                                        <div class="mt-4 flex {{ $effectiveAvailable ? 'justify-end' : 'justify-start' }}">
                                            @if(isset($qrTable) && $effectiveAvailable)
                                                <form method="POST" action="{{ route('client.table.cart.add', ['locale' => app()->getLocale(), 'code' => $qrCode]) }}" class="inline js-cart-form">
                                                    @csrf
                                                    <input type="hidden" name="menu_item_id" value="{{ $item->id }}">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-2 text-[11px] uppercase tracking-[0.25em] px-4 py-2 rounded-full border border-white/60 text-white/90 hover:bg-white/10 transition">
                                                        <img src="{{ asset('icons/coolicons/Interface/Shopping_Cart_01.svg') }}" alt="" class="w-4 h-4 invert opacity-80">
                                                        {{ __('menu-add-to-cart') ?? 'Добавить в корзину' }}
                                                    </button>
                                                </form>
                                            @else
                                                <button
                                                    type="button"
                                                    class="text-[11px] uppercase tracking-[0.25em] px-4 py-2 rounded-full
                                                           border {{ $effectiveAvailable ? 'border-white/40 text-white/80 hover:border-white hover:text-black hover:bg-white' : 'border-amber-400/60 text-amber-200 cursor-not-allowed opacity-70' }}
                                                           transition"
                                                    {{ $effectiveAvailable ? '' : 'disabled' }}
                                                >
                                                    {{ $effectiveAvailable ? __('menu-button-see-product') : __('menu-unavailable') }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-white/50">
                            {{ __('menu-no-position-category') }}
                        </p>
                    @endif
                </section>
            @empty
                <p class="text-sm md:text-base text-white/60 text-center mt-12">
                    {{ __('menu-no-all-category') }}
                </p>
            @endforelse
        </div>

        <button
            id="backToTop"
            type="button"
            class="hidden fixed bottom-6 md:bottom-6 right-5 md:right-6 z-40 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 text-white p-3 shadow-lg backdrop-blur"
            aria-label="{{ __('menu-button-to-categories') }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="w-5 h-5">
                <path d="M12 5l7 7m-7-7L5 12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 5v14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
        </button>

    </div>

    <div id="menu-item-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 backdrop-blur-sm">
        <div class="relative w-[82vw] max-w-sm md:max-w-md mx-auto rounded-3xl border border-white/15
                    bg-gradient-to-b from-white/10 via-black/90 to-black
                    p-4 md:p-6 shadow-[0_0_40px_rgba(0,0,0,1)]
                    max-h-[80svh] overflow-y-auto">

            <div class="flex flex-col md:flex-row gap-4">
                <div class="md:w-2/5">
                    <div class="w-full h-40 md:aspect-square md:h-auto rounded-2xl overflow-hidden bg-black/60 flex items-center justify-center border border-white/10">
                        <img id="menu-item-modal-image"
                             src=""
                             alt=""
                             class="w-full h-full object-cover hidden">
                        <div id="menu-item-modal-image-placeholder"
                             class="text-xs text-white/50 uppercase tracking-[0.2em]">
                            {{ __('menu-no-image') }}
                        </div>
                    </div>
                </div>

                <div class="md:w-3/5 flex flex-col gap-2">
                    <p id="menu-item-modal-category"
                       class="text-[10px] uppercase tracking-[0.25em] text-white/50">
                        {{ __('menu-category-title') }}
                    </p>

                    <h3 id="menu-item-modal-name"
                        class="text-lg md:text-xl font-semibold text-white drop-shadow-[0_0_10px_rgba(255,255,255,0.6)]">
                        {{ __('menu-item-title') }}
                    </h3>

                    <p id="menu-item-modal-price"
                       class="text-sm font-medium text-white/90">
                        {{ __('menu-money-title') }}
                    </p>

                    <p id="menu-item-modal-desc"
                       class="text-xs md:text-sm text-white/75 mt-1">
                        {{ __('menu-item-content') }}
                    </p>

                    <div id="menu-item-modal-allergens-wrapper" class="mt-3 hidden">
                        <p class="text-[10px] uppercase tracking-[0.25em] text-amber-300/90 mb-1">
                            {{ __('menu-allergens-title') }}
                        </p>
                        <p id="menu-item-modal-allergens"
                           class="text-xs md:text-sm text-amber-200/90"></p>
                    </div>
                </div>
            </div>

            <div class="mt-5 flex justify-end">
                <button type="button"
                        class="js-close-menu-item text-[11px] uppercase tracking-[0.25em] px-5 py-2 rounded-full
                               border border-white/50 text-white/80 hover:bg-white hover:text-black transition">
                    {{ __('menu-button-close-product') }}
                </button>
            </div>
        </div>
    </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('menu-item-modal')
    const closeButtons = document.querySelectorAll('.js-close-menu-item')

    const nameEl = document.getElementById('menu-item-modal-name')
    const descEl = document.getElementById('menu-item-modal-desc')
    const allergensWrapperEl = document.getElementById('menu-item-modal-allergens-wrapper')
    const allergensEl = document.getElementById('menu-item-modal-allergens')
    const priceEl = document.getElementById('menu-item-modal-price')
    const categoryEl = document.getElementById('menu-item-modal-category')
    const imageEl = document.getElementById('menu-item-modal-image')
    const imagePlaceholderEl = document.getElementById('menu-item-modal-image-placeholder')

    function openModal(data) {
        nameEl.textContent = data.name || ''
        descEl.textContent = data.desc || ''
        priceEl.textContent = data.price || ''
        categoryEl.textContent = data.category || ''

        if (data.allergens && data.allergens.trim() !== '') {
            allergensEl.textContent = data.allergens
            allergensWrapperEl.classList.remove('hidden')
        } else {
            allergensEl.textContent = ''
            allergensWrapperEl.classList.add('hidden')
        }

        if (data.image) {
            imageEl.src = data.image
            imageEl.alt = data.name || ''
            imageEl.classList.remove('hidden')
            imagePlaceholderEl.classList.add('hidden')
        } else {
            imageEl.src = ''
            imageEl.alt = ''
            imageEl.classList.add('hidden')
            imagePlaceholderEl.classList.remove('hidden')
        }

        modal.classList.remove('hidden')
        modal.classList.add('flex')
    }

    function closeModal() {
        modal.classList.add('hidden')
        modal.classList.remove('flex')
    }

    function openFromEl(el) {
        const data = {
            name: el.getAttribute('data-name'),
            desc: el.getAttribute('data-desc'),
            price: el.getAttribute('data-price'),
            image: el.getAttribute('data-image'),
            category: el.getAttribute('data-category'),
            allergens: el.getAttribute('data-allergens') || ''
        }
        openModal(data)
    }

    document.addEventListener('click', function (e) {
        const cartForm = e.target.closest('.js-cart-form')
        if (cartForm) return
        const card = e.target.closest('.js-open-menu-card')
        if (!card) return
        openFromEl(card)
    })

    closeButtons.forEach(function (btn) {
        btn.addEventListener('click', closeModal)
    })

    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal()
    })

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeModal()

        if (e.key !== 'Enter' && e.key !== ' ') return
        const el = document.activeElement
        if (!el || !el.classList || !el.classList.contains('js-open-menu-card')) return
        e.preventDefault()
        openFromEl(el)
    })

    const ua = navigator.userAgent || ''
    const isIOS = /iP(hone|od|ad)/.test(ua) || (ua.includes('Mac') && navigator.maxTouchPoints > 1)
    const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches

    let rafId = 0
    let stopScroll = false

    function stopSmooth() {
        stopScroll = true
        if (rafId) cancelAnimationFrame(rafId)
        rafId = 0
    }

    ;['touchstart','wheel','keydown'].forEach(function (ev) {
        window.addEventListener(ev, stopSmooth, { passive: true })
    })

    function headerOffset() {
        const header = document.querySelector('header')
        return (header ? header.offsetHeight + 20 : 80)
    }

function easeOutSine(t) {
  return Math.sin((t * Math.PI) / 2)
}

    function smoothScrollToY(targetY) {
        if (prefersReduced) {
            window.scrollTo(0, targetY)
            return
        }

        const startY = window.pageYOffset || document.documentElement.scrollTop || 0
        const maxY = Math.max(0, (document.documentElement.scrollHeight || 0) - window.innerHeight)
        const endY = Math.min(Math.max(0, targetY), maxY)
        const dist = endY - startY

        if (Math.abs(dist) < 2) {
            window.scrollTo(0, endY)
            return
        }

        stopScroll = false
        const dur = Math.max(380, Math.min(780, Math.abs(dist) / 2.2))
        const startT = performance.now()

        function step(now) {
            if (stopScroll) return
            const t = Math.min(1, (now - startT) / dur)
            const y = startY + dist * easeOutSine(t)
            window.scrollTo(0, y)
            if (t < 1) rafId = requestAnimationFrame(step)
        }

        rafId = requestAnimationFrame(step)
    }

    function scrollToTarget(target) {
        const y = target.getBoundingClientRect().top + (window.pageYOffset || 0) - headerOffset()
        if (isIOS) smoothScrollToY(y)
        else window.scrollTo({ top: y, behavior: prefersReduced ? 'auto' : 'smooth' })
    }

    const anchorLinks = document.querySelectorAll('a[href="#menu-top"], a[href^="#category-"]')
    anchorLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href') || ''
            if (!href.startsWith('#')) return
            const targetId = href.substring(1)
            const target = document.getElementById(targetId)
            if (!target) return
            e.preventDefault()
            scrollToTarget(target)
        })
    })

    const backBtn = document.getElementById('backToTop')
    if (backBtn) {
        const toggle = () => {
            const show = window.scrollY > 200
            backBtn.classList.toggle('hidden', !show)
        }
        toggle()
        window.addEventListener('scroll', toggle)

        backBtn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: prefersReduced ? 'auto' : 'smooth' })
        })
    }
})
</script>
@endpush

@endsection
