@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-ground text-ink pt-24 pb-16 flex items-center justify-center">
        <div class="max-w-md w-full px-6">

            <div class="text-center mb-8">
                <p class="text-xs text-ink-muted mb-3">
                    {{ __('reset-password-title') }}
                </p>
                <h1 class="logo-font text-3xl md:text-4xl mb-2">
                    {{ __('perigee-name') }}
                </h1>
                <p class="text-sm text-ink-muted">
                    {{ __('reset-password-content') }}
                </p>
            </div>

            <div class="rounded-2xl border border-line bg-surface  p-6 md:p-7">

                <form method="POST" action="{{ route('password.store') }}">
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <!-- Email Address -->
                    <div class="mb-4">
                        <label for="email" class="block text-xs text-ink-muted mb-2">
                            {{ __('register-email') }}
                        </label>
                        <input id="email"
                               type="email"
                               name="email"
                               value="{{ old('email', $request->email) }}"
                               required
                               autofocus
                               autocomplete="username"
                               class="w-full bg-ground/40 border border-line rounded-full px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-ink focus:ring-1 focus:ring-line-strong" />

                        @error('email')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="block text-xs text-ink-muted mb-2">
                            {{ __('register-password') }}
                        </label>
                        <input id="password"
                               type="password"
                               name="password"
                               required
                               autocomplete="new-password"
                               class="w-full bg-ground/40 border border-line rounded-full px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-ink focus:ring-1 focus:ring-line-strong" />

                        @error('password')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-xs text-ink-muted mb-2">
                            {{ __('confirm-password') }}
                        </label>
                        <input id="password_confirmation"
                               type="password"
                               name="password_confirmation"
                               required
                               autocomplete="new-password"
                               class="w-full bg-ground/40 border border-line rounded-full px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-ink focus:ring-1 focus:ring-line-strong" />

                        @error('password_confirmation')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-3">
                        <button type="submit"
                                class="inline-flex min-h-12 w-full items-center justify-center rounded-full bg-nebula px-6 font-bold text-on-nebula transition hover:bg-ink">
                            {{ __('reset-password-button') }}
                        </button>

                        <p class="text-xs text-center text-ink-muted mt-1">
                            <a href="{{ route('login') }}" class="text-ink hover:underline">
                                {{ __('back-to-login') }}
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
