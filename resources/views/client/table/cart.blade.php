@extends('layouts.app')

@php
$locale = app()->getLocale();
@endphp

@section('content')
<div class="w-full min-h-screen bg-gradient-to-b from-black via-[#050816] to-black text-white pt-24 pb-16">
    <div class="max-w-4xl mx-auto px-4 md:px-6">
        <div class="mb-8">
            <a href="{{ route('client.table.menu', ['locale' => $locale, 'code' => $code]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 text-sm text-white/80 transition">
                <img src="{{ asset('icons/coolicons/Arrow/Chevron_Left.svg') }}" alt="" class="w-4 h-4 invert opacity-80">
                <span>{{ __('menu-back') }}</span>
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 text-sm text-emerald-300 border border-emerald-500/60 bg-emerald-500/10 rounded-2xl">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 text-sm text-red-300 border border-red-500/60 bg-red-500/10 rounded-2xl">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-12">
            <h1 class="logo-font text-4xl md:text-5xl mb-2 drop-shadow-[0_0_14px_rgba(255,255,255,0.9)]">{{ __('cart-title') }}</h1>
            <p class="text-base text-white/70">{{ $table->name }}</p>
        </div>

        @if(count($cartItems) > 0)
            <div class="space-y-6">
                <!-- Items list -->
                <div class="space-y-4">
                    @foreach($cartItems as $itemId => $row)
                        <div class="rounded-2xl border border-white/10 bg-white/5 overflow-hidden hover:border-white/20 hover:bg-white/10 transition">
                            <div class="flex flex-col gap-4 p-4">
                                <!-- Top: Image and Info -->
                                <div class="flex items-start gap-4">
                                    <!-- Image -->
                                    <div class="shrink-0 w-20 h-20 rounded-xl overflow-hidden bg-black/50 border border-white/10">
                                        @if($row['item']->image_path)
                                            <img src="{{ asset('storage/' . $row['item']->image_path) }}"
                                                 alt="{{ $row['item']->name[$locale] ?? 'Dish' }}"
                                                 class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-white/30">
                                                <img src="{{ asset('icons/coolicons/Media/Image_01.svg') }}" alt="" class="w-10 h-10 invert opacity-30">
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Info -->
                                    <div class="flex-1 min-w-0">
                                        <p class="text-base font-semibold text-white">
                                            {{ $row['item']->name[$locale] ?? '—' }}
                                        </p>
                                        <p class="text-sm text-white/60 mt-1">
                                            {{ number_format($row['item']->price, 2, '.', ' ') }} € × {{ $row['qty'] }}
                                        </p>
                                        <p class="text-lg font-bold text-white mt-2">
                                            {{ number_format($row['sum'], 2, '.', ' ') }} €
                                        </p>
                                    </div>
                                </div>

                                <!-- Bottom: Quantity controls and delete -->
                                <div class="flex items-center justify-between gap-2 pt-2 border-t border-white/10">
                                    <div class="flex items-center gap-1 bg-black/60 rounded-lg border border-white/10 p-1">
                                        <form method="POST" action="{{ route('client.table.cart.update', ['locale' => $locale, 'code' => $code, 'itemId' => $itemId]) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="qty" value="{{ $row['qty'] - 1 }}">
                                            <button type="submit" class="inline-flex items-center justify-center w-6 h-6 rounded text-white/70 hover:text-white hover:bg-white/10 transition"
                                                    {{ $row['qty'] <= 1 ? 'disabled style=opacity:0.5;cursor:not-allowed;' : '' }}>
                                                <img src="{{ asset('icons/coolicons/Edit/Remove_Minus.svg') }}" alt="-" class="w-4 h-4 invert opacity-70">
                                            </button>
                                        </form>

                                        <span class="text-sm font-semibold text-white w-5 text-center">{{ $row['qty'] }}</span>

                                        <form method="POST" action="{{ route('client.table.cart.update', ['locale' => $locale, 'code' => $code, 'itemId' => $itemId]) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="qty" value="{{ $row['qty'] + 1 }}">
                                            <button type="submit" class="inline-flex items-center justify-center w-6 h-6 rounded text-white/70 hover:text-white hover:bg-white/10 transition">
                                                <img src="{{ asset('icons/coolicons/Edit/Add_Plus.svg') }}" alt="+" class="w-4 h-4 invert opacity-70">
                                            </button>
                                        </form>
                                    </div>

                                    <form method="POST" action="{{ route('client.table.cart.remove', ['locale' => $locale, 'code' => $code, 'itemId' => $itemId]) }}" class="inline ml-auto">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white/60 hover:text-white hover:bg-white/10 border border-white/20 transition">
                                            <img src="{{ asset('icons/coolicons/Interface/Trash_Full.svg') }}" alt="" class="w-4 h-4 invert opacity-60">
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Summary panel -->
                <div class="rounded-2xl border border-white/20 bg-white/5 p-6">
                    <div class="mb-6">
                        <div class="flex items-center justify-between">
                            <span class="text-base font-semibold text-white">{{ __('cart-total') }}</span>
                            <span class="text-2xl font-bold text-white drop-shadow-[0_0_8px_rgba(255,255,255,0.5)]">{{ number_format($cartTotal, 2, '.', ' ') }} €</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('client.table.cart.confirm', ['locale' => $locale, 'code' => $code]) }}">
                        @csrf
                        <button type="submit"
                                class="w-full px-6 py-3 rounded-full border border-white/80 bg-white/10 hover:bg-white/20 text-xs uppercase tracking-[0.2em] text-white transition drop-shadow-[0_0_10px_rgba(255,255,255,0.4)]">
                            <span class="flex items-center justify-center gap-2">
                                <img src="{{ asset('icons/coolicons/Interface/Check_Big.svg') }}" alt="" class="w-5 h-5 invert">
                                {{ __('cart-confirm') }}
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="rounded-2xl border border-white/10 bg-white/5 p-12 md:p-16 text-center">
                <img src="{{ asset('icons/coolicons/Interface/Shopping_Cart_01.svg') }}" alt="" class="w-16 h-16 mx-auto mb-6 invert opacity-30">
                <h2 class="text-2xl font-semibold text-white mb-2">{{ __('cart-empty') }}</h2>
                <p class="text-white/60 mb-6">{{ __('cart-add-dishes') }}</p>
                <a href="{{ route('client.table.menu', ['locale' => $locale, 'code' => $code]) }}"
                   class="inline-flex items-center gap-2 px-8 py-3 rounded-full border border-white/80 bg-white/10 hover:bg-white/20 text-xs uppercase tracking-[0.2em] text-white transition drop-shadow-[0_0_10px_rgba(255,255,255,0.4)]">
                    <img src="{{ asset('icons/coolicons/Edit/Add_Plus.svg') }}" alt="" class="w-5 h-5 invert">
                    {{ __('cart-to-menu') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
