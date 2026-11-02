@extends('layouts.admin')

@section('content')
    <div class="mb-8">
        <h1 class="logo-font text-3xl md:text-4xl mb-2">
            {{ __('admin-users-create-title') }}
        </h1>
        <p class="text-sm text-ink-muted">
            {{ __('admin-users-create-subtitle') }}
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-6 bg-red-950/60 border border-red-500/60 text-red-100 text-sm rounded-xl px-4 py-3">
            <p class="font-semibold mb-2 tracking-wide">
                {{ __('admin-users-form-errors-title') }}
            </p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li class="text-sm">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-surface border border-line rounded-2xl px-6 py-6 md:px-8 md:py-7 max-w-3xl">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="space-y-1.5">
                <label for="name" class="block text-xs text-ink-muted">
                    {{ __('admin-users-field-name') }}
                </label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 z-10 flex items-center text-ink-muted">
                        <x-icon category="User" name="User_01" class="w-5 h-5"/>
                    </span>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        class="w-full bg-ground/60 border border-line rounded-xl pl-11 pr-3 py-2.5 text-sm text-ink
                               focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/70"
                        placeholder="{{ __('admin-users-field-name-placeholder') }}"
                        required
                    >
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="email" class="block text-xs text-ink-muted">
                    {{ __('admin-users-field-email') }}
                </label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 z-10 flex items-center text-ink-muted">
                        <x-icon category="Communication" name="Mail" class="w-5 h-5"/>
                    </span>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="w-full bg-ground/60 border border-line rounded-xl pl-11 pr-3 py-2.5 text-sm text-ink
                               focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/70"
                        placeholder="{{ __('admin-users-field-email-placeholder') }}"
                        required
                    >
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="role" class="block text-xs text-ink-muted">
                    {{ __('admin-users-field-role') }}
                </label>

                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 z-10 flex items-center text-ink-soft">
                        <x-icon category="User" name="User_Card_ID" class="w-5 h-5"/>
                    </span>

                    <select
                        id="role"
                        name="role"
                        class="appearance-none w-full bg-ground/60 border border-line rounded-xl pl-11 pr-10 py-2.5
                               text-sm text-ink focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/70"
                        required
                    >
                        <option value="" disabled {{ old('role') ? '' : 'selected' }}>
                            {{ __('admin-users-field-role-placeholder') }}
                        </option>
                        <option value="admin"  {{ old('role') === 'admin' ? 'selected' : '' }}>
                            {{ __('admin-users-role-label-admin') }}
                        </option>
                        <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>
                            {{ __('admin-users-role-label-staff') }}
                        </option>
                        <option value="client" {{ old('role') === 'client' ? 'selected' : '' }}>
                            {{ __('admin-users-role-label-client') }}
                        </option>
                    </select>

                    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-ink-muted">
                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="none">
                            <path d="M5 7l5 5 5-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="password" class="block text-xs text-ink-muted">
                    {{ __('admin-users-field-password') }}
                </label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 z-10 flex items-center text-ink-muted">
                        <x-icon category="Interface" name="Lock" class="w-5 h-5"/>
                    </span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="w-full bg-ground/60 border border-line rounded-xl pl-11 pr-3 py-2.5 text-sm text-ink
                               focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/70"
                        placeholder="{{ __('admin-users-field-password-placeholder') }}"
                        required
                    >
                </div>
            </div>

            <div class="pt-2 flex flex-wrap items-center gap-3">
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full border border-red-500/80 bg-red-600/80
                               text-xs text-ink
                               hover:bg-red-500 transition">
                    <x-icon category="System" name="Save" class="w-5 h-5"/>
                    {{ __('admin-users-create-submit') }}
                </button>

                <a href="{{ route('admin.users.index') }}"
                   class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full border border-line-strong
                          text-xs text-ink-muted
                          hover:text-ink hover:border-line-strong hover:bg-surface transition">
                    <x-icon category="Edit" name="Close_Square" class="w-5 h-5"/>
                    {{ __('admin-users-cancel-button') }}
                </a>
            </div>
        </form>
    </div>
@endsection
