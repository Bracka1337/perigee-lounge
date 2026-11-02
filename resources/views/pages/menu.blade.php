@extends('layouts.app')

@section('seo_key', 'menu')

@section('content')
    <div id="menu-top" class="mx-auto min-h-screen max-w-7xl scroll-mt-24 px-6 pb-20 pt-28">
        @if (session('success'))
            <div role="status" class="mb-6 rounded-xl border border-emerald-500/60 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div role="alert" class="mb-6 rounded-xl border border-red-500/60 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                {{ session('error') }}
            </div>
        @endif

        <x-page-header :eyebrow="__('perigee-rest-name')" :title="__('header-menu')" :lead="__('menu-title')" />

        @isset($qrTable)
            <a href="{{ route('client.table.cart.show', ['locale' => app()->getLocale(), 'code' => $qrCode]) }}"
               class="mb-10 flex items-center justify-between gap-4 rounded-2xl border border-nebula bg-surface p-5 transition hover:border-ink">
                <span>
                    <span class="block font-bold">{{ __('qr-table-order-title') }}</span>
                    <span class="mt-1 block text-sm text-ink-muted">{{ __('qr-table-name-label') }}: {{ $qrTable->name }}</span>
                </span>
                <img src="{{ asset('icons/coolicons/Interface/Shopping_Cart_01.svg') }}" alt="" class="h-5 w-5 shrink-0 invert">
            </a>
        @endisset

        @if ($categories->isNotEmpty())
            <nav aria-label="{{ __('menu-button-to-categories') }}"
                 class="sticky top-[69px] z-30 -mx-6 mb-10 flex gap-2 overflow-x-auto border-b border-line bg-ground/95 px-6 py-3 text-sm backdrop-blur">
                <a href="#menu-top" data-menu-chip
                   class="flex min-h-11 shrink-0 items-center rounded-full border border-line-strong px-5 transition hover:border-ink aria-[current=true]:border-ink aria-[current=true]:bg-ink aria-[current=true]:font-bold aria-[current=true]:text-on-nebula"
                   aria-current="true">
                    {{ __('menu-button-all-product') }}
                </a>

                @foreach ($categories as $category)
                    <a href="#category-{{ $category->id }}" data-menu-chip
                       class="flex min-h-11 shrink-0 items-center rounded-full border px-5 transition hover:border-ink aria-[current=true]:border-ink aria-[current=true]:bg-ink aria-[current=true]:font-bold aria-[current=true]:text-on-nebula {{ $category->is_special ? 'border-nebula text-nebula' : 'border-line-strong' }}">
                        {{ $category->t('name') }}
                    </a>
                @endforeach
            </nav>
        @endif

        @forelse ($categories as $category)
            @php
                $catName = $category->t('name');
                $catDesc = $category->t('description');
                $catAvailable = $category->is_available ?? true;
            @endphp

            <section id="category-{{ $category->id }}" data-menu-section class="mb-14 scroll-mt-40">
                <div class="mb-5 flex flex-wrap items-baseline justify-between gap-x-6 gap-y-2">
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-2xl font-bold {{ $category->is_special ? 'text-nebula' : '' }}">{{ $catName }}</h2>

                        @unless ($catAvailable)
                            <span class="rounded-full border border-amber-400/70 px-3 py-1 text-xs text-amber-200">{{ __('menu-unavailable') }}</span>
                        @endunless

                        @if ($category->is_special)
                            <span class="rounded-full border border-nebula px-3 py-1 text-xs text-nebula">{{ __('menu-special-category') }}</span>
                        @endif
                    </div>

                    @if ($catDesc)
                        <p class="text-sm text-ink-muted">{{ $catDesc }}</p>
                    @endif
                </div>

                @if ($category->items->isNotEmpty())
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($category->items as $item)
                            @php
                                $itemName = $item->t('name');
                                $itemDesc = $item->t('description');
                                $isAvailable = $catAvailable && ($item->is_available ?? true);
                            @endphp

                            <article class="flex flex-col overflow-hidden rounded-2xl border bg-surface {{ $category->is_special ? 'border-nebula/60' : 'border-line' }} {{ $isAvailable ? '' : 'opacity-70' }}">
                                @if ($item->image_path)
                                    <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $itemName }}" loading="lazy" class="h-44 w-full object-cover">
                                @endif

                                <div class="flex flex-1 flex-col p-6">
                                    <div class="mb-2 flex justify-between gap-4">
                                        <h3 class="text-lg font-bold">{{ $itemName }}</h3>
                                        <span class="whitespace-nowrap font-bold {{ $isAvailable ? '' : 'text-ink-muted line-through' }}">{{ $item->formatted_price }}</span>
                                    </div>

                                    @if ($itemDesc)
                                        <p class="leading-relaxed text-ink-muted">{{ $itemDesc }}</p>
                                    @endif

                                    <div class="mt-auto flex flex-wrap items-center justify-end gap-2 pt-4">
                                        @if ($isAvailable)
                                            <button type="button" data-menu-item
                                                    data-name="{{ $itemName }}"
                                                    data-desc="{{ $itemDesc }}"
                                                    data-price="{{ $item->formatted_price }}"
                                                    data-image="{{ $item->image_path ? asset('storage/'.$item->image_path) : '' }}"
                                                    data-category="{{ $catName }}"
                                                    data-allergens="{{ $item->t('allergens') }}"
                                                    class="min-h-11 rounded-full border border-line-strong px-5 text-sm transition hover:border-ink">
                                                {{ __('menu-button-see-product') }}
                                            </button>

                                            @isset($qrTable)
                                                <form method="POST" action="{{ route('client.table.cart.add', ['locale' => app()->getLocale(), 'code' => $qrCode]) }}">
                                                    @csrf
                                                    <input type="hidden" name="menu_item_id" value="{{ $item->id }}">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" class="min-h-11 rounded-full bg-nebula px-5 text-sm font-bold text-on-nebula transition hover:bg-ink">
                                                        {{ __('menu-add-to-cart') }}
                                                    </button>
                                                </form>
                                            @endisset
                                        @else
                                            <span class="text-sm text-amber-200">{{ __('menu-unavailable') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="text-ink-muted">{{ __('menu-no-position-category') }}</p>
                @endif
            </section>
        @empty
            <p class="mt-12 text-center text-ink-muted">{{ __('menu-no-all-category') }}</p>
        @endforelse
    </div>

    <dialog id="menu-item-modal" aria-labelledby="menu-item-modal-name"
            class="m-auto w-[min(32rem,calc(100vw-2rem))] rounded-2xl border border-line-strong bg-surface p-0 text-ink backdrop:bg-ground/80">
        <img id="menu-item-modal-image" alt="" class="hidden h-52 w-full object-cover">

        <div class="p-6">
            <p id="menu-item-modal-category" class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-nebula"></p>
            <div class="mb-3 flex justify-between gap-4">
                <h2 id="menu-item-modal-name" class="text-xl font-bold"></h2>
                <p id="menu-item-modal-price" class="whitespace-nowrap font-bold"></p>
            </div>
            <p id="menu-item-modal-desc" class="leading-relaxed text-ink-soft"></p>

            <div id="menu-item-modal-allergens-wrapper" class="mt-4 hidden border-t border-line pt-4">
                <p class="mb-1 text-sm font-bold text-amber-200">{{ __('menu-allergens-title') }}</p>
                <p id="menu-item-modal-allergens" class="text-sm text-ink-soft"></p>
            </div>

            <form method="dialog" class="mt-6 flex justify-end">
                <button class="min-h-11 rounded-full border border-line-strong px-6 text-sm transition hover:border-ink">
                    {{ __('menu-button-close-product') }}
                </button>
            </form>
        </div>
    </dialog>
@endsection
