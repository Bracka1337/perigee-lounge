@extends('layouts.client')

@section('client-header')
    <div class="mb-10">
        <p class="text-xs text-ink-muted mb-3">
            {{ __('client-dashboard-badge') }}
        </p>
        <h1 class="logo-font text-3xl md:text-4xl mb-2">
            {{ __('client-dashboard-title', ['name' => auth()->user()->name]) }}
        </h1>
        <p class="text-sm md:text-base text-ink-muted">
            {{ __('client-dashboard-subtitle') }}
        </p>
    </div>
@endsection

@section('client-content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="rounded-2xl border border-line bg-surface p-5 ">
            <h2 class="text-xs text-ink-muted mb-2">
                {{ __('client-dashboard-reservations-title') }}
            </h2>
            <p class="text-ink-soft text-sm">
                {{ __('client-dashboard-reservations-text') }}
            </p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-5 ">
            <h2 class="text-xs text-ink-muted mb-2">
                {{ __('client-dashboard-orders-title') }}
            </h2>
            <p class="text-ink-soft text-sm">
                {{ __('client-dashboard-orders-text') }}
            </p>
        </div>
    </div>
@endsection
