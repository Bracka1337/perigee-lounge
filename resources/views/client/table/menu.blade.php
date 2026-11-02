@extends('layouts.app')

@php
    $locale = app()->getLocale();
@endphp

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-6">
        <div class="mb-4">
            <a href="{{ route('menu', ['locale' => $locale]) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 text-sm text-white/80 transition">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="w-4 h-4">
                    <path d="M19 12H5m5-7l-7 7 7 7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>{{ __('menu-back') ?? 'Назад к меню' }}</span>
            </a>
        </div>

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

        <div class="mb-6">
            <h1 class="logo-font text-3xl md:text-4xl mb-1">{{ __('menu-title') ?? 'Меню' }}</h1>
            <p class="text-sm text-white/70">Стол: {{ $table->name }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 space-y-6">
                @foreach($categories as $category)
                    <div>
                        <h2 class="text-xs uppercase tracking-[0.22em] text-white/60 mb-3">{{ $category->name[$locale] ?? $category->name['ru'] ?? $category->name }}</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($category->items as $item)
                                <div class="rounded-xl border border-white/10 bg-white/5 p-3 flex gap-3">
                                    @if(($item->image_path ?? null))
                                        <img src="{{ asset('storage/'.$item->image_path) }}" alt="" class="w-16 h-16 rounded-lg object-cover">
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium truncate">{{ $item->name[$locale] ?? '' }}</div>
                                        <div class="text-[11px] text-white/60">{{ number_format($item->price, 2, '.', ' ') }} €</div>
                                        <form method="POST" action="{{ route('client.table.cart.add', ['locale' => $locale, 'code' => request()->route('code')]) }}" class="mt-2 flex items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="menu_item_id" value="{{ $item->id }}">
                                            <input type="number" name="quantity" value="1" min="1" max="99" class="w-16 bg-black/60 border border-white/20 rounded-lg px-2 py-1 text-xs text-white focus:outline-none focus:ring-1 focus:ring-white/70">
                                            <button type="submit" class="px-4 py-1.5 rounded-full border border-white/60 bg-white/5 hover:bg-white/15 text-[10px] uppercase tracking-[0.15em] text-white/90 transition">{{ __('add-to-cart') ?? 'Добавить' }}</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <h3 class="text-xs uppercase tracking-[0.22em] text-white/60 mb-3">{{ __('cart-title') ?? 'Корзина' }}</h3>
                    <div class="space-y-2">
                        @php $total = 0; @endphp
                        @forelse($cart as $menuItemId => $qty)
                            @php $m = \App\Models\MenuItem::find($menuItemId); $sum = ($m?->price ?? 0) * $qty; $total += $sum; @endphp
                            <div class="flex items-center justify-between gap-3 px-3 py-2 rounded-lg bg-black/20">
                                <div class="min-w-0 text-xs truncate">{{ $m?->name[$locale] ?? '—' }}</div>
                                <div class="text-[11px] text-white/70">x{{ $qty }} · {{ number_format($sum, 2, '.', ' ') }} €</div>
                            </div>
                        @empty
                            <p class="text-xs text-white/60">{{ __('cart-empty') ?? 'Корзина пуста' }}</p>
                        @endforelse
                    </div>

                    <div class="h-px bg-white/10 my-3"></div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-white/70">{{ __('cart-total') ?? 'Итого' }}</span>
                        <span class="font-medium">{{ number_format($total, 2, '.', ' ') }} €</span>
                    </div>

                    <form method="POST" action="{{ route('client.table.cart.confirm', ['locale' => $locale, 'code' => request()->route('code')]) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2.5 rounded-full border border-white/70 bg-white/10 hover:bg-white/20 text-[11px] uppercase tracking-[0.18em] text-white transition drop-shadow-[0_0_8px_rgba(255,255,255,0.3)]">{{ __('cart-confirm') ?? 'Подтвердить' }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
