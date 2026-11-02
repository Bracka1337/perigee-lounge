@extends('layouts.app')

@section('content')
    <div class="w-full bg-ground text-ink min-h-screen pt-24 pb-16">
        <div class="max-w-7xl mx-auto px-6">
            @hasSection('client-header')
                @yield('client-header')
            @endif

            <div class="flex flex-col md:flex-row gap-8">
                @include('client.partials.sidebar')

                <main class="min-w-0 flex-1">
                    @if (session('status'))
                        <div class="mb-6 text-sm text-emerald-400">
                            {{ session('status') }}
                        </div>
                    @endif

                    @yield('client-content')
                </main>
            </div>
        </div>
    </div>
@endsection
