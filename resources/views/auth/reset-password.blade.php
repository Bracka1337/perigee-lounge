@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-black text-white pt-24 pb-16 flex items-center justify-center">
        <div class="max-w-md w-full px-6">

            <div class="text-center mb-8">
                <p class="text-xs tracking-[0.35em] uppercase text-white/60 mb-3">
                    {{ __('reset-password-title') }}
                </p>
                <h1 class="logo-font text-3xl md:text-4xl mb-2 drop-shadow-[0_0_10px_rgba(255,255,255,0.7)]">
                    {{ __('perigee-name') }}
                </h1>
                <p class="text-sm text-white/70">
                    {{ __('reset-password-content') }}
                </p>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/5 shadow-2xl shadow-black/70 p-6 md:p-7">

                <form method="POST" action="{{ route('password.store') }}">
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <!-- Email Address -->
                    <div class="mb-4">
                        <label for="email" class="block text-xs uppercase tracking-[0.25em] text-white/60 mb-2">
                            {{ __('register-email') }}
                        </label>
                        <input id="email"
                               type="email"
                               name="email"
                               value="{{ old('email', $request->email) }}"
                               required
                               autofocus
                               autocomplete="username"
                               class="w-full bg-black/40 border border-white/20 rounded-full px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-white focus:ring-1 focus:ring-white/70" />

                        @error('email')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="block text-xs uppercase tracking-[0.25em] text-white/60 mb-2">
                            {{ __('register-password') }}
                        </label>
                        <input id="password"
                               type="password"
                               name="password"
                               required
                               autocomplete="new-password"
                               class="w-full bg-black/40 border border-white/20 rounded-full px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-white focus:ring-1 focus:ring-white/70" />

                        @error('password')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-xs uppercase tracking-[0.25em] text-white/60 mb-2">
                            {{ __('confirm-password') }}
                        </label>
                        <input id="password_confirmation"
                               type="password"
                               name="password_confirmation"
                               required
                               autocomplete="new-password"
                               class="w-full bg-black/40 border border-white/20 rounded-full px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-white focus:ring-1 focus:ring-white/70" />

                        @error('password_confirmation')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-3">
                        <button type="submit"
                                class="w-full inline-flex justify-center px-6 py-2.5 rounded-full border border-white/80
                                   text-[11px] tracking-[0.25em] uppercase text-white
                                   shadow-[0_0_14px_rgba(255,255,255,0.6)]
                                   hover:bg-white hover:text-black transition">
                            {{ __('reset-password-button') }}
                        </button>

                        <p class="text-[11px] text-center text-white/50 mt-1">
                            <a href="{{ route('login') }}" class="text-white hover:underline">
                                {{ __('back-to-login') }}
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
