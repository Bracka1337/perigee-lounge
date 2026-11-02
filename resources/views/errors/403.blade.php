@extends('errors.layout')

@section('code', '403')
@section('title', __('403-title'))

@section('actions')
    <a href="{{ route('home', ['locale' => app()->getLocale()]) }}"
       class="inline-flex min-h-13 items-center rounded-full bg-nebula px-8 font-bold text-on-nebula transition hover:bg-ink">
        {{ __('to-main-button') }}
    </a>
    @auth
        <a href="{{ route('dashboard', ['locale' => app()->getLocale()]) }}"
           class="inline-flex min-h-13 items-center rounded-full border border-line-strong px-8 font-medium transition hover:border-ink">
            {{ __('to-profile-button') }}
        </a>
    @else
        <a href="{{ route('login', ['locale' => app()->getLocale()]) }}"
           class="inline-flex min-h-13 items-center rounded-full border border-line-strong px-8 font-medium transition hover:border-ink">
            {{ __('log-in-button') }}
        </a>
    @endauth
@endsection
