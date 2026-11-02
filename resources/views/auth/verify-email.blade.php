@extends('layouts.app')

@section('content')
    <div class="w-full bg-ground text-ink min-h-screen pt-28 pb-16">
        <div class="max-w-6xl mx-auto px-6">
            <div class="max-w-xl mx-auto rounded-2xl border border-line bg-surface p-6 md:p-8 ">
                <p class="text-xs text-ink-muted mb-3">
                    {{ __('perigee-rest-name') }}
                </p>

                <h1 class="logo-font text-2xl md:text-3xl mb-2">
                    {{ __('perigee-name') }}
                </h1>

                <p class="text-xs md:text-sm text-ink-muted mb-6">
                    {{ __('perigee-verif-email') }}
                </p>

                @if (session('warning'))
                    <div class="mb-6 rounded-2xl border border-orange-500/60 bg-orange-500/10 px-4 py-3 text-xs md:text-sm text-orange-100">
                        {{ session('warning') }}
                    </div>
                @endif

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-6 rounded-2xl border border-emerald-500/60 bg-emerald-500/10 px-4 py-3 text-xs md:text-sm text-emerald-100">
                        {{ __('verif-email-new-link') }}
                    </div>
                @endif

                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <form method="POST" action="{{ route('verification.send') }}" class="w-full md:w-auto">
                        @csrf
                        <button type="submit"
                                class="w-full md:w-auto inline-flex items-center justify-center rounded-full border border-line-strong px-5 py-2.5 text-xs md:text-xs text-ink hover:bg-ink hover:text-on-nebula transition">
                            {{ __('verif-resend-button') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}" class="w-full md:w-auto text-center md:text-right">
                        @csrf
                        <button type="submit"
                                class="text-xs md:text-xs text-ink-muted hover:text-ink underline transition">
                            {{ __('header-logout') }}
                        </button>
                    </form>
                </div>

                <p class="mt-6 text-xs text-ink-muted">
                    {{ __('verif-email-spam') }}
                </p>
            </div>
        </div>
    </div>
@endsection
