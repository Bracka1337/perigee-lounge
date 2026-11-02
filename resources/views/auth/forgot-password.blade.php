@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-ground text-ink pt-24 pb-16 flex items-center justify-center">
        <div class="max-w-md w-full px-6">

            <div class="text-center mb-8">
                <p class="text-xs text-ink-muted mb-3">
                    {{ __('forgot-password-recovery') }}
                </p>
                <h1 class="logo-font text-3xl md:text-4xl mb-2">
                    {{ __('forgot-password-title') }}
                </h1>
                <p class="text-sm text-ink-muted">
                    {{ __('forgot-password-content') }}
                </p>
            </div>

            <div class="rounded-2xl border border-line bg-surface  p-6 md:p-7">


                <x-auth-session-status class="mb-4 text-sm text-emerald-300" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="mb-6">
                        <label for="email" class="block text-xs text-ink-muted mb-2">
                            {{ __('register-email') }}
                        </label>
                        <input id="email"
                               type="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               class="w-full bg-ground/40 border border-line rounded-full px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-ink focus:ring-1 focus:ring-line-strong" />

                        @error('email')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-3">
                        <button type="submit"
                                class="inline-flex min-h-12 w-full items-center justify-center rounded-full bg-nebula px-6 font-bold text-on-nebula transition hover:bg-ink">
                            {{ __('recovery-button') }}
                        </button>

                        <p class="text-xs text-center text-ink-muted mt-1">
                            {{ __('register-password-remember') }}
                            <a href="{{ route('login') }}" class="text-ink hover:underline">
                                {{ __('log-in-button') }}
                            </a>
                        </p>
                    </div>
                </form>
            </div>

        </div>
    </div>
@endsection
