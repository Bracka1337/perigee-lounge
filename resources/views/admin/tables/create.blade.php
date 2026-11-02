@extends('layouts.admin')

@section('content')
    <div class="mb-8">
        <h1 class="logo-font text-3xl md:text-4xl mb-2 drop-shadow-[0_0_10px_rgba(255,255,255,0.7)]">
            {{ __('admin-tables-create-title') }}
        </h1>
        <p class="text-sm text-white/70">
            {{ __('admin-tables-create-subtitle') }}
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-6 bg-red-950/60 border border-red-500/60 text-red-100 text-sm rounded-xl px-4 py-3">
            <p class="font-semibold mb-2 tracking-wide">
                {{ __('admin-tables-form-errors-title') }}
            </p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li class="text-[13px]">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white/5 border border-white/10 rounded-2xl px-6 py-6 md:px-8 md:py-7 max-w-3xl">
        <form action="{{ route('admin.tables.store', ['locale' => app()->getLocale()]) }}" method="POST" class="space-y-5">
            @csrf

            <div class="space-y-1.5">
                <label for="name" class="block text-xs uppercase tracking-[0.25em] text-white/60">
                    {{ __('admin-tables-field-name') }}
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    class="w-full bg-black/60 border border-white/20 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/70"
                    required
                >
            </div>

            <div class="space-y-1.5">
                <label for="capacity" class="block text-xs uppercase tracking-[0.25em] text-white/60">
                    {{ __('admin-tables-field-capacity') }}
                </label>
                <input
                    type="number"
                    id="capacity"
                    name="capacity"
                    value="{{ old('capacity', 2) }}"
                    min="1"
                    max="20"
                    class="w-full bg-black/60 border border-white/20 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/70"
                    required
                >
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="pos_x" class="block text-xs uppercase tracking-[0.25em] text-white/60">
                        {{ __('admin-tables-field-pos-x') }}
                    </label>
                    <input
                        type="number"
                        id="pos_x"
                        name="pos_x"
                        value="{{ old('pos_x', 10) }}"
                        min="0"
                        max="100"
                        step="0.01"
                        class="w-full bg-black/60 border border-white/20 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/70"
                    >
                </div>

                <div class="space-y-1.5">
                    <label for="pos_y" class="block text-xs uppercase tracking-[0.25em] text-white/60">
                        {{ __('admin-tables-field-pos-y') }}
                    </label>
                    <input
                        type="number"
                        id="pos_y"
                        name="pos_y"
                        value="{{ old('pos_y', 10) }}"
                        min="0"
                        max="100"
                        step="0.01"
                        class="w-full bg-black/60 border border-white/20 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/70"
                    >
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="width" class="block text-xs uppercase tracking-[0.25em] text-white/60">
                        {{ __('admin-tables-field-width') }}
                    </label>
                    <input
                        type="number"
                        id="width"
                        name="width"
                        value="{{ old('width', 80) }}"
                        min="10"
                        max="1000"
                        class="w-full bg-black/60 border border-white/20 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/70"
                    >
                </div>

                <div class="space-y-1.5">
                    <label for="height" class="block text-xs uppercase tracking-[0.25em] text-white/60">
                        {{ __('admin-tables-field-height') }}
                    </label>
                    <input
                        type="number"
                        id="height"
                        name="height"
                        value="{{ old('height', 80) }}"
                        min="10"
                        max="1000"
                        class="w-full bg-black/60 border border-white/20 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/70"
                    >
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="rotation" class="block text-xs uppercase tracking-[0.25em] text-white/60">
                    {{ __('admin-tables-field-rotation') }}
                </label>
                <input
                    type="number"
                    id="rotation"
                    name="rotation"
                    value="{{ old('rotation', 0) }}"
                    min="0"
                    max="359"
                    class="w-full bg-black/60 border border-white/20 rounded-xl px-3 py-2.5 text-sm text-white focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/70"
                >
            </div>

            <div class="flex items-center gap-2 mt-1">
                <input type="checkbox"
                       id="is_active"
                       name="is_active"
                       value="1"
                       class="w-4 h-4 rounded border-white/40 bg-black/60"
                       {{ old('is_active', true) ? 'checked' : '' }}>
                <label for="is_active" class="text-xs text-white/70">
                    {{ __('admin-tables-field-active') }}
                </label>
            </div>

            <div class="pt-2 flex flex-wrap items-center gap-3">
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full
                               border border-red-500/80 bg-red-600/80
                               text-[11px] tracking-[0.25em] uppercase text-white
                               shadow-[0_0_14px_rgba(248,113,113,0.7)]
                               hover:bg-red-500 transition">
                    <x-icon category="Interface" name="Save" class="w-5 h-5 shrink-0"/>
                    <span>{{ __('admin-tables-create-submit') }}</span>
                </button>

<a href="{{ route('admin.tables.index', ['locale' => app()->getLocale()]) }}"
   class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full
          border border-white/30
          text-[11px] tracking-[0.25em] uppercase text-white/70
          hover:text-white hover:border-white/70 hover:bg-white/5 transition">
    <svg class="w-5 h-5 shrink-0" viewBox="0 0 20 20" fill="none" aria-hidden="true">
        <path d="M6 6l8 8M14 6l-8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
    </svg>
    <span>{{ __('admin-tables-cancel-button') }}</span>
</a>

            </div>
        </form>
    </div>
@endsection
