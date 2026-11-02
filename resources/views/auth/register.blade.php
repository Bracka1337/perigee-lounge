@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-ground text-ink pt-24 pb-16 flex items-center justify-center">
        <div class="max-w-md w-full px-4 sm:px-6">

            <div class="text-center mb-8">
                <p class="text-xs text-ink-muted mb-3">
                    {{ __('register-in-title') }}
                </p>
                <h1 class="logo-font text-3xl md:text-4xl mb-2">
                    {{ __('perigee-name') }}
                </h1>
                <p class="text-sm text-ink-muted">
                    {{ __('register-in-content') }}
                </p>
            </div>

            <div class="rounded-2xl border border-line bg-surface  p-5 sm:p-6 md:p-7 overflow-hidden">

                <form method="POST" action="{{ route('register') }}" class="w-full">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="block text-xs text-ink-muted mb-2">
                            {{ __('register-name') }}
                        </label>
                        <input id="name"
                               type="text"
                               name="name"
                               value="{{ old('name') }}"
                               required
                               autofocus
                               autocomplete="name"
                               class="w-full bg-ground/40 border border-line rounded-full px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-ink focus:ring-1 focus:ring-line-strong" />

                        @error('name')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="block text-xs text-ink-muted mb-2">
                            {{ __('register-email') }}
                        </label>
                        <input id="email"
                               type="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autocomplete="username"
                               class="w-full bg-ground/40 border border-line rounded-full px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-ink focus:ring-1 focus:ring-line-strong" />

                        @error('email')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="birth_date" class="block text-xs text-ink-muted mb-2">
                            {{ __('register-birth-date') }}
                        </label>
                        <input id="birth_date"
                               type="date"
                               name="birth_date"
                               value="{{ old('birth_date') }}"
                               required
                               max="{{ \Carbon\Carbon::now()->subYears(20)->format('Y-m-d') }}"
                               class="w-full max-w-full bg-ground/40 border border-line rounded-full px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-ink focus:ring-1 focus:ring-line-strong
                                  [color-scheme:dark] appearance-none box-border" />
                        <p class="mt-1 text-xs text-ink-muted">{{ __('register-birth-date-hint') }}</p>

                        @error('birth_date')
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
                               autocomplete="new-password"
                               class="w-full bg-ground/40 border border-line rounded-full px-4 py-2.5 text-sm
                                  focus:outline-none focus:border-ink focus:ring-1 focus:ring-line-strong" />

                        @error('password')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-xs text-ink-muted mb-2">
                            {{ __('register-password-confirm') }}
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

                    <div class="mb-5">
                        <label for="terms" class="flex items-center gap-3 p-4 rounded-xl border border-line
                           bg-surface hover:border-line transition cursor-pointer group">
                            <div class="relative flex-shrink-0">
                                <input id="terms"
                                       type="checkbox"
                                       name="terms"
                                       value="1"
                                       required
                                       class="w-5 h-5 appearance-none bg-ground/40 border-2 border-line-strong rounded-lg
                                          checked:bg-nebula
                                          group-hover:border-line-strong focus:outline-none focus:ring-2 focus:ring-line-strong focus:ring-offset-2 focus:ring-offset-black
                                          cursor-pointer transition-all" />
                                <svg class="absolute w-3 h-3 top-1 left-1 text-on-nebula pointer-events-none hidden checked:block"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="flex-1 text-xs text-ink-muted group-hover:text-ink-soft transition">
                                <span class="block">{{ __('register-terms-agree') }}</span>
                                <a href="{{ route('terms') }}" class="text-ink-soft hover:text-ink underline transition text-xs inline-block w-fit">
                                    {{ __('register-terms-link') }}
                                </a>
                            </div>
                        </label>
                        @error('terms')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label for="newsletter" class="flex items-center gap-3 p-4 rounded-xl border border-line
                           bg-surface hover:border-line transition cursor-pointer group">
                            <div class="relative flex-shrink-0">
                                <input id="newsletter"
                                       type="checkbox"
                                       name="newsletter_agreed"
                                       value="1"
                                       class="w-5 h-5 appearance-none bg-ground/40 border-2 border-line-strong rounded-lg
                                          checked:bg-nebula
                                          group-hover:border-line-strong focus:outline-none focus:ring-2 focus:ring-line-strong focus:ring-offset-2 focus:ring-offset-black
                                          cursor-pointer transition-all" />
                                <svg class="absolute w-3 h-3 top-1 left-1 text-on-nebula pointer-events-none hidden checked:block"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="flex-1 text-xs text-ink-muted group-hover:text-ink-soft transition">
                                <span class="block mb-1">{{ __('register-newsletter-agree') }}</span>
                            </div>
                        </label>
                    </div>

                    <div class="flex flex-col gap-3">
                        <button type="submit"
                                class="inline-flex min-h-12 w-full items-center justify-center rounded-full bg-nebula px-6 font-bold text-on-nebula transition hover:bg-ink">
                            {{ __('register-in-button') }}
                        </button>

                        <p class="text-xs text-center text-ink-muted mt-1">
                            {{ __('yes-account') }}
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
