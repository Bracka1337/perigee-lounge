@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-ground text-ink pt-24 pb-16 flex items-center justify-center">
        <div class="max-w-md w-full px-6">

            <div class="text-center mb-8">
                <p class="text-xs text-ink-muted mb-3">
                    {{ __('log-in-title') }}
                </p>
                <h1 class="logo-font text-3xl md:text-4xl mb-2">
                    {{ __('perigee-name') }}
                </h1>
                <p class="text-sm text-ink-muted">
                    {{ __('log-in-content') }}
                </p>
            </div>

            <div class="rounded-2xl border border-line bg-surface  p-6 md:p-7">

                <x-auth-session-status class="mb-4 text-sm text-emerald-300" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="block text-xs text-ink-muted mb-2">
                            {{ __('register-email') }}
                        </label>
                        <input id="email"
                               type="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               autocomplete="username"
                               class="w-full bg-ground/40 border border-line rounded-full px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-ink focus:ring-1 focus:ring-line-strong" />

                        @error('email')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-xs text-ink-muted mb-2">
                            {{ __('register-password') }}
                        </label>
                        <input id="password"
                               type="password"
                               name="password"
                               required
                               autocomplete="current-password"
                               class="w-full bg-ground/40 border border-line rounded-full px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-ink focus:ring-1 focus:ring-line-strong" />

                        @error('password')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <label for="remember_me" class="flex items-center gap-2 text-xs text-ink-muted">
{{--                            <input id="remember_me"--}}
{{--                                   type="checkbox"--}}
{{--                                   name="remember"--}}
{{--                                   class="rounded border-line-strong bg-ground/60 text-white--}}
{{--                                      focus:ring-line-strong focus:ring-offset-0">--}}
{{--                            <span>Запомнить меня</span>--}}
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-xs text-ink-muted hover:text-ink transition underline-offset-4 hover:underline">
                                {{ __('forgot-password-title') }}
                            </a>
                        @endif
                    </div>

                    <div class="flex flex-col gap-3">
                        <button type="submit"
                                class="inline-flex min-h-12 w-full items-center justify-center rounded-full bg-nebula px-6 font-bold text-on-nebula transition hover:bg-ink">
                            {{ __('log-in-button') }}
                        </button>

                        @if (Route::has('register'))
                            <p class="text-xs text-center text-ink-muted mt-1">
                                {{ __('no-account') }}
                                <a href="{{ route('register') }}" class="text-ink hover:underline">
                                    {{ __('register-title') }}
                                </a>
                            </p>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
