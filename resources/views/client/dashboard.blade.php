@extends('layouts.client')

@section('client-header')
    <div class="mb-10">
        <p class="text-xs tracking-[0.35em] uppercase text-white/60 mb-3">
            {{ __('client-dashboard-badge') }}
        </p>
        <h1 class="logo-font text-3xl md:text-4xl mb-2 drop-shadow-[0_0_10px_rgba(255,255,255,0.7)]">
            {{ __('client-dashboard-title', ['name' => auth()->user()->name]) }}
        </h1>
        <p class="text-sm md:text-base text-white/70">
            {{ __('client-dashboard-subtitle') }}
        </p>
    </div>
@endsection

@section('client-content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="rounded-3xl border border-white/10 bg-white/5 p-5 shadow-xl shadow-black/60">
            <h2 class="text-xs uppercase tracking-[0.25em] text-white/60 mb-2">
                {{ __('client-dashboard-reservations-title') }}
            </h2>
            <p class="text-white/80 text-sm">
                {{ __('client-dashboard-reservations-text') }}
            </p>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/5 p-5 shadow-xl shadow-black/60">
            <h2 class="text-xs uppercase tracking-[0.25em] text-white/60 mb-2">
                {{ __('client-dashboard-orders-title') }}
            </h2>
            <p class="text-white/80 text-sm">
                {{ __('client-dashboard-orders-text') }}
            </p>
        </div>
    </div>
@endsection
