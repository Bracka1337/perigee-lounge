@extends('layouts.admin')

@section('content')
    <div class="mb-8">
        <h1 class="logo-font text-3xl md:text-4xl mb-2">
            {{ __('admin-tables-edit-title') }}
        </h1>
        <p class="text-sm text-ink-muted">
            {{ __('admin-tables-edit-subtitle', ['name' => $table->name]) }}
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-6 bg-red-950/60 border border-red-500/60 text-red-100 text-sm rounded-xl px-4 py-3">
            <p class="font-semibold mb-2 tracking-wide">
                {{ __('admin-tables-form-errors-title') }}
            </p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li class="text-sm">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-surface border border-line rounded-2xl px-6 py-6 md:px-8 md:py-7 max-w-3xl">
        <form action="{{ route('admin.tables.update', ['locale' => app()->getLocale(), 'table' => $table]) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="space-y-1.5">
                <label for="name" class="block text-xs text-ink-muted">
                    {{ __('admin-tables-field-name') }}
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $table->name) }}"
                    class="w-full bg-ground/60 border border-line rounded-xl px-3 py-2.5 text-sm text-ink focus:outline-none focus:border-nebula focus:ring-1 focus:ring-nebula/70"
                    required
                >
            </div>

            <div class="space-y-1.5">
                <label for="capacity" class="block text-xs text-ink-muted">
                    {{ __('admin-tables-field-capacity') }}
                </label>
                <input
                    type="number"
                    id="capacity"
                    name="capacity"
                    value="{{ old('capacity', $table->capacity) }}"
                    min="1"
                    max="20"
                    class="w-full bg-ground/60 border border-line rounded-xl px-3 py-2.5 text-sm text-ink focus:outline-none focus:border-nebula focus:ring-1 focus:ring-nebula/70"
                    required
                >
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="pos_x" class="block text-xs text-ink-muted">
                        {{ __('admin-tables-field-pos-x') }}
                    </label>
                    <input
                        type="number"
                        id="pos_x"
                        name="pos_x"
                        value="{{ old('pos_x', $table->pos_x) }}"
                        min="0"
                        max="100"
                        step="0.01"
                        class="w-full bg-ground/60 border border-line rounded-xl px-3 py-2.5 text-sm text-ink focus:outline-none focus:border-nebula focus:ring-1 focus:ring-nebula/70"
                    >
                </div>

                <div class="space-y-1.5">
                    <label for="pos_y" class="block text-xs text-ink-muted">
                        {{ __('admin-tables-field-pos-y') }}
                    </label>
                    <input
                        type="number"
                        id="pos_y"
                        name="pos_y"
                        value="{{ old('pos_y', $table->pos_y) }}"
                        min="0"
                        max="100"
                        step="0.01"
                        class="w-full bg-ground/60 border border-line rounded-xl px-3 py-2.5 text-sm text-ink focus:outline-none focus:border-nebula focus:ring-1 focus:ring-nebula/70"
                    >
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="width" class="block text-xs text-ink-muted">
                        {{ __('admin-tables-field-width') }}
                    </label>
                    <input
                        type="number"
                        id="width"
                        name="width"
                        value="{{ old('width', $table->width) }}"
                        min="10"
                        max="1000"
                        class="w-full bg-ground/60 border border-line rounded-xl px-3 py-2.5 text-sm text-ink focus:outline-none focus:border-nebula focus:ring-1 focus:ring-nebula/70"
                    >
                </div>

                <div class="space-y-1.5">
                    <label for="height" class="block text-xs text-ink-muted">
                        {{ __('admin-tables-field-height') }}
                    </label>
                    <input
                        type="number"
                        id="height"
                        name="height"
                        value="{{ old('height', $table->height) }}"
                        min="10"
                        max="1000"
                        class="w-full bg-ground/60 border border-line rounded-xl px-3 py-2.5 text-sm text-ink focus:outline-none focus:border-nebula focus:ring-1 focus:ring-nebula/70"
                    >
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="rotation" class="block text-xs text-ink-muted">
                    {{ __('admin-tables-field-rotation') }}
                </label>
                <input
                    type="number"
                    id="rotation"
                    name="rotation"
                    value="{{ old('rotation', $table->rotation) }}"
                    min="0"
                    max="359"
                    class="w-full bg-ground/60 border border-line rounded-xl px-3 py-2.5 text-sm text-ink focus:outline-none focus:border-nebula focus:ring-1 focus:ring-nebula/70"
                >
            </div>

<div class="pt-2 flex flex-wrap items-center gap-3">
    <button type="submit"
            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full
                   border border-nebula bg-nebula
                   text-xs font-semibold text-on-nebula
                   hover:border-ink hover:bg-ink transition">
        <x-icon category="Interface" name="Save" class="w-5 h-5 shrink-0"/>
        <span>{{ __('admin-tables-save-button') }}</span>
    </button>

    <a href="{{ route('admin.tables.index', ['locale' => app()->getLocale()]) }}"
       class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full
              border border-line-strong
              text-xs text-ink-muted
              hover:text-ink hover:border-line-strong hover:bg-surface transition">
        <svg class="w-5 h-5 shrink-0" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M6 6l8 8M14 6l-8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        <span>{{ __('admin-tables-cancel-button') }}</span>
    </a>
</div>

        </form>
    </div>
@endsection
