@extends('layouts.app')

@php
$locale = app()->getLocale();
@endphp

@section('content')
<div class="w-full min-h-screen bg-ground text-ink pt-24 pb-16">
    <div class="max-w-4xl mx-auto px-4 md:px-6">
        <div class="mb-8">
            <a href="{{ route('client.table.menu', ['locale' => $locale, 'code' => $code]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-surface hover:bg-line border border-line text-sm text-ink-soft transition">
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
            <h1 class="logo-font text-4xl md:text-5xl mb-2">{{ __('cart-title') }}</h1>
            <p class="text-base text-ink-muted">{{ $table->name }}</p>
        </div>

        @unless($hasOpenOrder)
            <div class="mb-6 p-4 text-sm text-ink-soft border border-line-strong bg-surface rounded-2xl" role="status">
                {{ __('table-no-open-order-hint') }}
            </div>
        @endunless

        @if(count($cartItems) > 0)
            <div class="space-y-6">
                <!-- Items list -->
                <div class="space-y-4">
                    @foreach($cartItems as $itemId => $row)
                        <div class="rounded-2xl border border-line bg-surface overflow-hidden hover:border-line hover:bg-line transition">
                            <div class="flex flex-col gap-4 p-4">
                                <!-- Top: Image and Info -->
                                <div class="flex items-start gap-4">
                                    <!-- Image -->
                                    <div class="shrink-0 w-20 h-20 rounded-xl overflow-hidden bg-ground/50 border border-line">
                                        @if($row['item']->image_path)
                                            <img src="{{ asset('storage/' . $row['item']->image_path) }}"
                                                 alt="{{ $row['item']->name[$locale] ?? 'Dish' }}"
                                                 class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-ink-muted">
                                                <img src="{{ asset('icons/coolicons/Media/Image_01.svg') }}" alt="" class="w-10 h-10 invert opacity-30">
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Info -->
                                    <div class="flex-1 min-w-0">
                                        <p class="text-base font-semibold text-ink">
                                            {{ $row['item']->name[$locale] ?? '—' }}
                                        </p>
                                        <p class="text-sm text-ink-muted mt-1">
                                            {{ number_format($row['item']->price, 2, '.', ' ') }} € × {{ $row['qty'] }}
                                        </p>
                                        <p class="text-lg font-bold text-ink mt-2">
                                            {{ number_format($row['sum'], 2, '.', ' ') }} €
                                        </p>
                                    </div>
                                </div>

                                <!-- Bottom: Quantity controls and delete -->
                                <div class="flex items-center justify-between gap-2 pt-2 border-t border-line">
                                    <div class="flex items-center gap-1 bg-ground/60 rounded-lg border border-line p-1">
                                        <form method="POST" action="{{ route('client.table.cart.update', ['locale' => $locale, 'code' => $code, 'itemId' => $itemId]) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="qty" value="{{ $row['qty'] - 1 }}">
                                            <button type="submit" class="inline-flex items-center justify-center w-6 h-6 rounded text-ink-muted hover:text-ink hover:bg-line transition"
                                                    {{ $row['qty'] <= 1 ? 'disabled style=opacity:0.5;cursor:not-allowed;' : '' }}>
                                                <img src="{{ asset('icons/coolicons/Edit/Remove_Minus.svg') }}" alt="-" class="w-4 h-4 invert opacity-70">
                                            </button>
                                        </form>

                                        <span class="text-sm font-semibold text-ink w-5 text-center">{{ $row['qty'] }}</span>

                                        <form method="POST" action="{{ route('client.table.cart.update', ['locale' => $locale, 'code' => $code, 'itemId' => $itemId]) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="qty" value="{{ $row['qty'] + 1 }}">
                                            <button type="submit" class="inline-flex items-center justify-center w-6 h-6 rounded text-ink-muted hover:text-ink hover:bg-line transition">
                                                <img src="{{ asset('icons/coolicons/Edit/Add_Plus.svg') }}" alt="+" class="w-4 h-4 invert opacity-70">
                                            </button>
                                        </form>
                                    </div>

                                    <form method="POST" action="{{ route('client.table.cart.remove', ['locale' => $locale, 'code' => $code, 'itemId' => $itemId]) }}" class="inline ml-auto">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-ink-muted hover:text-ink hover:bg-line border border-line transition">
                                            <img src="{{ asset('icons/coolicons/Interface/Trash_Full.svg') }}" alt="" class="w-4 h-4 invert opacity-60">
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Summary panel -->
                <div class="rounded-2xl border border-line bg-surface p-6">
                    <div class="mb-6">
                        <div class="flex items-center justify-between">
                            <span class="text-base font-semibold text-ink">{{ __('cart-total') }}</span>
                            <span class="text-2xl font-bold text-ink">{{ number_format($cartTotal, 2, '.', ' ') }} €</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('client.table.cart.confirm', ['locale' => $locale, 'code' => $code]) }}">
                        @csrf
                        <button type="submit"
                                class="w-full px-6 py-3 rounded-full border border-line-strong bg-line hover:bg-line text-xs text-ink transition">
                            <span class="flex items-center justify-center gap-2">
                                <img src="{{ asset('icons/coolicons/Interface/Check_Big.svg') }}" alt="" class="w-5 h-5 invert">
                                {{ __('cart-confirm') }}
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="rounded-2xl border border-line bg-surface p-12 md:p-16 text-center">
                <img src="{{ asset('icons/coolicons/Interface/Shopping_Cart_01.svg') }}" alt="" class="w-16 h-16 mx-auto mb-6 invert opacity-30">
                <h2 class="text-2xl font-semibold text-ink mb-2">{{ __('cart-empty') }}</h2>
                <p class="text-ink-muted mb-6">{{ __('cart-add-dishes') }}</p>
                <a href="{{ route('client.table.menu', ['locale' => $locale, 'code' => $code]) }}"
                   class="inline-flex items-center gap-2 px-8 py-3 rounded-full border border-line-strong bg-line hover:bg-line text-xs text-ink transition">
                    <img src="{{ asset('icons/coolicons/Edit/Add_Plus.svg') }}" alt="" class="w-5 h-5 invert">
                    {{ __('cart-to-menu') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
