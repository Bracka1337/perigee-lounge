@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-black text-white pt-24 pb-16 flex items-center justify-center">
        <div class="max-w-md w-full px-4 sm:px-6">

            <div class="text-center mb-8">
                <p class="text-xs tracking-[0.35em] uppercase text-white/60 mb-3">
                    {{ __('register-in-title') }}
                </p>
                <h1 class="logo-font text-3xl md:text-4xl mb-2 drop-shadow-[0_0_10px_rgba(255,255,255,0.7)]">
                    {{ __('perigee-name') }}
                </h1>
                <p class="text-sm text-white/70">
                    {{ __('register-in-content') }}
                </p>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/5 shadow-2xl shadow-black/70 p-5 sm:p-6 md:p-7 overflow-hidden">

                <form method="POST" action="{{ route('register') }}" class="w-full">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="block text-xs uppercase tracking-[0.25em] text-white/60 mb-2">
                            {{ __('register-name') }}
                        </label>
                        <input id="name"
                               type="text"
                               name="name"
                               value="{{ old('name') }}"
                               required
                               autofocus
                               autocomplete="name"
                               class="w-full bg-black/40 border border-white/20 rounded-full px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-white focus:ring-1 focus:ring-white/70" />

                        @error('name')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="block text-xs uppercase tracking-[0.25em] text-white/60 mb-2">
                            {{ __('register-email') }}
                        </label>
                        <input id="email"
                               type="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autocomplete="username"
                               class="w-full bg-black/40 border border-white/20 rounded-full px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-white focus:ring-1 focus:ring-white/70" />

                        @error('email')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="birth_date" class="block text-xs uppercase tracking-[0.25em] text-white/60 mb-2">
                            {{ __('register-birth-date') }}
                        </label>
                        <input id="birth_date"
                               type="date"
                               name="birth_date"
                               value="{{ old('birth_date') }}"
                               required
                               max="{{ \Carbon\Carbon::now()->subYears(20)->format('Y-m-d') }}"
                               class="w-full max-w-full bg-black/40 border border-white/20 rounded-full px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-white focus:ring-1 focus:ring-white/70
                                  [color-scheme:dark] appearance-none box-border" />
                        <p class="mt-1 text-[10px] text-white/50">{{ __('register-birth-date-hint') }}</p>

                        @error('birth_date')
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
                               autocomplete="new-password"
                               class="w-full bg-black/40 border border-white/20 rounded-full px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-white focus:ring-1 focus:ring-white/70" />

                        @error('password')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-xs uppercase tracking-[0.25em] text-white/60 mb-2">
                            {{ __('register-password-confirm') }}
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

                    <div class="mb-5">
                        <label for="terms" class="flex items-center gap-3 p-4 rounded-xl border border-white/10
                           bg-gradient-to-r from-white/5 to-white/[0.02] hover:border-white/20 transition cursor-pointer group">
                            <div class="relative flex-shrink-0">
                                <input id="terms"
                                       type="checkbox"
                                       name="terms"
                                       value="1"
                                       required
                                       class="w-5 h-5 appearance-none bg-black/40 border-2 border-white/30 rounded-lg
                                          checked:bg-gradient-to-br checked:from-white checked:to-white/80
                                          group-hover:border-white/50 focus:outline-none focus:ring-2 focus:ring-white/70 focus:ring-offset-2 focus:ring-offset-black
                                          cursor-pointer transition-all" />
                                <svg class="absolute w-3 h-3 top-1 left-1 text-black pointer-events-none hidden checked:block"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="flex-1 text-xs text-white/70 group-hover:text-white/90 transition">
                                <span class="block">{{ __('register-terms-agree') }}</span>
                                <a href="{{ route('terms') }}" class="text-white/80 hover:text-white underline transition text-[10px] inline-block w-fit">
                                    {{ __('register-terms-link') }}
                                </a>
                            </div>
                        </label>
                        @error('terms')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label for="newsletter" class="flex items-center gap-3 p-4 rounded-xl border border-white/10
                           bg-gradient-to-r from-white/5 to-white/[0.02] hover:border-white/20 transition cursor-pointer group">
                            <div class="relative flex-shrink-0">
                                <input id="newsletter"
                                       type="checkbox"
                                       name="newsletter_agreed"
                                       value="1"
                                       class="w-5 h-5 appearance-none bg-black/40 border-2 border-white/30 rounded-lg
                                          checked:bg-gradient-to-br checked:from-white checked:to-white/80
                                          group-hover:border-white/50 focus:outline-none focus:ring-2 focus:ring-white/70 focus:ring-offset-2 focus:ring-offset-black
                                          cursor-pointer transition-all" />
                                <svg class="absolute w-3 h-3 top-1 left-1 text-black pointer-events-none hidden checked:block"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="flex-1 text-xs text-white/70 group-hover:text-white/90 transition">
                                <span class="block mb-1">{{ __('register-newsletter-agree') }}</span>
                            </div>
                        </label>
                    </div>

                    <div class="flex flex-col gap-3">
                        <button type="submit"
                                class="w-full inline-flex justify-center px-6 py-2.5 rounded-full border border-white/80
                                   text-[11px] tracking-[0.25em] uppercase text-white
                                   shadow-[0_0_14px_rgba(255,255,255,0.6)]
                                   hover:bg-white hover:text-black transition">
                            {{ __('register-in-button') }}
                        </button>

                        <p class="text-[11px] text-center text-white/50 mt-1">
                            {{ __('yes-account') }}
                            <a href="{{ route('login') }}" class="text-white hover:underline">
                                {{ __('log-in-button') }}
                            </a>
                        </p>
                    </div>
                </form>
            </div>

        </div>
    </div>
@endsection
