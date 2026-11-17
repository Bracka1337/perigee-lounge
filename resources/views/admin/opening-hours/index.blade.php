@extends('layouts.admin')

@section('content')
    <div class="mb-8">
        <h1 class="logo-font text-2xl md:text-3xl lg:text-4xl mb-2">{{ __('admin-tables-opening-hours-title') }}</h1>
        <p class="text-xs md:text-sm text-ink-muted">{{ __('admin-opening-hours-subtitle') }}</p>
    </div>

    @if (session('status'))
        <div class="mb-6 bg-emerald-950/60 border border-emerald-500/70 text-emerald-100 text-xs md:text-sm rounded-xl px-4 py-3" role="status">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-red-950/60 border border-red-500/60 text-red-100 text-xs md:text-sm rounded-xl px-4 py-3" role="alert">
            @foreach (array_unique($errors->all()) as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="max-w-2xl rounded-2xl border border-line bg-ground/70 p-4 md:p-6">
        <form action="{{ route('admin.opening-hours.update', ['locale' => app()->getLocale()]) }}" method="POST" class="space-y-3">
            @csrf
            @php
                $weekdays = [
                    1 => __('admin-weekday-mon'),
                    2 => __('admin-weekday-tue'),
                    3 => __('admin-weekday-wed'),
                    4 => __('admin-weekday-thu'),
                    5 => __('admin-weekday-fri'),
                    6 => __('admin-weekday-sat'),
                    0 => __('admin-weekday-sun'),
                ];
            @endphp

            @foreach ($weekdays as $dayIndex => $label)
                @php
                    $row = $hours[$dayIndex] ?? null;
                @endphp

                <div class="flex flex-col gap-2 text-sm sm:flex-row sm:items-center">
                    <div class="shrink-0 text-ink sm:w-28">{{ $label }}</div>

                    <label class="flex min-h-11 shrink-0 items-center gap-2 text-ink-muted sm:w-28">
                        <input type="checkbox" name="hours[{{ $dayIndex }}][is_open]" value="1"
                               class="h-5 w-5 rounded border-line-strong bg-ground/60" @checked($row?->is_open)>
                        {{ __('admin-tables-opening-hours-open') }}
                    </label>

                    <div class="flex w-full min-w-0 items-center gap-2 sm:w-auto">
                        <input type="time" name="hours[{{ $dayIndex }}][opens_at]" aria-label="{{ $label }}: {{ __('admin-opening-hours-opens') }}"
                               value="{{ $row?->opens_at ? \Illuminate\Support\Str::of($row->opens_at)->substr(0, 5) : '' }}"
                               class="w-full min-w-0 rounded-lg border border-line bg-ground/60 px-3 text-ink sm:w-32">
                        <span class="shrink-0 text-ink-muted">—</span>
                        <input type="time" name="hours[{{ $dayIndex }}][closes_at]" aria-label="{{ $label }}: {{ __('admin-opening-hours-closes') }}"
                               value="{{ $row?->closes_at ? \Illuminate\Support\Str::of($row->closes_at)->substr(0, 5) : '' }}"
                               class="w-full min-w-0 rounded-lg border border-line bg-ground/60 px-3 text-ink sm:w-32">
                    </div>
                </div>
            @endforeach

            <div class="pt-3">
                <button type="submit"
                        class="inline-flex min-h-11 items-center gap-2 rounded-full bg-nebula px-5 text-xs font-semibold text-on-nebula transition hover:bg-ink">
                    {{ __('admin-tables-opening-hours-save-button') }}
                </button>
            </div>
        </form>
    </div>
@endsection
