@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-black text-white pt-24 pb-16 flex items-center justify-center">
        <div class="max-w-md w-full px-6">

            <div class="text-center mb-8">
                <p class="text-xs tracking-[0.35em] uppercase text-white/60 mb-3">
                    {{ __('log-in-title') }}
                </p>
                <h1 class="logo-font text-3xl md:text-4xl mb-2 drop-shadow-[0_0_10px_rgba(255,255,255,0.7)]">
                    {{ __('perigee-name') }}
                </h1>
                <p class="text-sm text-white/70">
                    {{ __('log-in-content') }}
                </p>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/5 shadow-2xl shadow-black/70 p-6 md:p-7">

                <x-auth-session-status class="mb-4 text-sm text-emerald-300" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="block text-xs uppercase tracking-[0.25em] text-white/60 mb-2">
                            {{ __('register-email') }}
                        </label>
                        <input id="email"
                               type="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               autocomplete="username"
                               class="w-full bg-black/40 border border-white/20 rounded-full px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-white focus:ring-1 focus:ring-white/70" />

                        @error('email')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-xs uppercase tracking-[0.25em] text-white/60 mb-2">
                            {{ __('register-password') }}
                        </label>
                        <input id="password"
                               type="password"
                               name="password"
                               required
                               autocomplete="current-password"
                               class="w-full bg-black/40 border border-white/20 rounded-full px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-white focus:ring-1 focus:ring-white/70" />

                        @error('password')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <label for="remember_me" class="flex items-center gap-2 text-xs text-white/60">
{{--                            <input id="remember_me"--}}
{{--                                   type="checkbox"--}}
{{--                                   name="remember"--}}
{{--                                   class="rounded border-white/40 bg-black/60 text-white--}}
{{--                                      focus:ring-white focus:ring-offset-0">--}}
{{--                            <span>Запомнить меня</span>--}}
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-xs text-white/60 hover:text-white transition underline-offset-4 hover:underline">
                                {{ __('forgot-password-title') }}
                            </a>
                        @endif
                    </div>

                    <div class="flex flex-col gap-3">
                        <button type="submit"
                                class="w-full inline-flex justify-center px-6 py-2.5 rounded-full border border-white/80
                                   text-[11px] tracking-[0.25em] uppercase text-white
                                   shadow-[0_0_14px_rgba(255,255,255,0.6)]
                                   hover:bg-white hover:text-black transition">
                            {{ __('log-in-button') }}
                        </button>

                        @if (Route::has('register'))
                            <p class="text-[11px] text-center text-white/50 mt-1">
                                {{ __('no-account') }}
                                <a href="{{ route('register') }}" class="text-white hover:underline">
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
