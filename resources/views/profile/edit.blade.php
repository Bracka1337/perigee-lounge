@extends('layouts.client')

@section('client-header')
    <div class="mb-10">
        <p class="text-xs text-ink-muted mb-3">
            {{ __('client-profile-badge') }}
        </p>

        <h1 class="logo-font text-3xl md:text-4xl mb-2">
            {{ __('client-profile-title') }}
        </h1>

        <p class="text-sm md:text-base text-ink-muted">
            {{ __('client-profile-subtitle') }}
        </p>
    </div>
@endsection

@section('client-content')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        {{-- Профиль --}}
        <div class="rounded-2xl border border-line bg-surface p-5 ">
            <h2 class="text-xs text-ink-muted mb-4 inline-flex items-center gap-2">
                <x-icon category="User" name="User_01" class="w-5 h-5"/>
                {{ __('client-profile-section-data-title') }}
            </h2>

            <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('patch')

                <div>
                    <label class="block text-xs text-ink-muted mb-1" for="name">
                        {{ __('client-profile-name-label') }}
                    </label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        class="w-full rounded-2xl bg-ground/40 border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-line-strong"
                        value="{{ old('name', auth()->user()->name) }}"
                        required
                        autofocus
                    >
                    @error('name')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs text-ink-muted mb-1" for="email">
                        {{ __('client-profile-email-label') }}
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        class="w-full rounded-2xl bg-ground/40 border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-line-strong"
                        value="{{ old('email', auth()->user()->email) }}"
                        required
                    >
                    @error('email')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror

                    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                        <p class="mt-2 text-xs text-yellow-300">
                            {{ __('client-profile-email-unverified') }}
                        </p>
                    @endif
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-line-strong px-4 py-2 text-xs font-semibold hover:bg-line transition"
                    >
                        <x-icon category="System" name="Save" class="w-5 h-5"/>
                        {{ __('client-profile-save-button') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Смена пароля --}}
        <div class="rounded-2xl border border-line bg-surface p-5 ">
            <h2 class="text-xs text-ink-muted mb-4 inline-flex items-center gap-2">
                <x-icon category="Interface" name="Lock" class="w-5 h-5"/>
                {{ __('client-profile-password-section-title') }}
            </h2>

            <form method="post" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                @method('put')

                <div>
                    <label class="block text-xs text-ink-muted mb-1" for="current_password">
                        {{ __('client-profile-current-password-label') }}
                    </label>
                    <input
                        id="current_password"
                        name="current_password"
                        type="password"
                        class="w-full rounded-2xl bg-ground/40 border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-line-strong"
                        autocomplete="current-password"
                    >
                    @error('current_password', 'updatePassword')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs text-ink-muted mb-1" for="password">
                        {{ __('client-profile-new-password-label') }}
                    </label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="w-full rounded-2xl bg-ground/40 border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-line-strong"
                        autocomplete="new-password"
                    >
                    @error('password', 'updatePassword')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs text-ink-muted mb-1" for="password_confirmation">
                        {{ __('client-profile-new-password-confirm-label') }}
                    </label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        class="w-full rounded-2xl bg-ground/40 border border-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-line-strong"
                        autocomplete="new-password"
                    >
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-line-strong px-4 py-2 text-xs font-semibold hover:bg-line transition"
                    >
                        <x-icon category="Arrows" name="Arrow_Reload_02" class="w-5 h-5"/>
                        {{ __('client-profile-password-button') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
