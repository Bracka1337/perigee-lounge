@extends('errors.layout')

@section('code', '404')
@section('title', __('404-title'))

@section('actions')
    <a href="{{ route('home', ['locale' => app()->getLocale()]) }}"
       class="inline-flex min-h-13 items-center rounded-full bg-nebula px-8 font-bold text-on-nebula transition hover:bg-ink">
        {{ __('to-main-button') }}
    </a>
    <a href="{{ route('menu', ['locale' => app()->getLocale()]) }}"
       class="inline-flex min-h-13 items-center rounded-full border border-line-strong px-8 font-medium transition hover:border-ink">
        {{ __('perigee-menu-button') }}
    </a>
@endsection
