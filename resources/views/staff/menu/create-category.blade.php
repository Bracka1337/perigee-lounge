@extends('layouts.staff')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="logo-font text-2xl mb-2">
                {{ __('staff-menu-category-create-title') }}
            </h1>
            <p class="text-sm text-white/60">
                {{ __('staff-menu-category-create-subtitle') }}
            </p>
        </div>

        <a href="{{ route('staff.menu-categories.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-full border border-white/10 bg-black/30 text-[11px] uppercase tracking-[0.18em] text-white/70 hover:text-white hover:border-white/25 transition">
            <x-icon category="Arrow" name="Arrow_Undo_Up_Left" class="w-5 h-5"/>
            <span>{{ __('staff-back-to-menu') }}</span>
        </a>
    </div>

    @php
        $locales = config('site.locales');
        $default = config('site.default_locale');
    @endphp

    <form method="POST" action="{{ route('staff.menu-categories.store') }}" class="max-w-2xl text-xs">
        @csrf

        <div class="rounded-3xl border border-white/15 bg-black/40 backdrop-blur-xl shadow-[0_0_30px_rgba(0,0,0,0.35)] overflow-hidden">
            <div class="p-5 md:p-6 space-y-6">

                  <div class="flex flex-wrap gap-2" id="category-lang-tabs">
                    @foreach($locales as $locale)
                        <button type="button"
                            class="lang-tab-category inline-flex items-center gap-2 px-3 py-1.5 rounded-full border text-[11px] uppercase tracking-[0.18em]
                                {{ $loop->first ? 'bg-red-600/80 text-white border-red-400 shadow-[0_0_10px_rgba(248,113,113,0.45)]' : 'bg-black/40 text-white/60 border-white/30 hover:text-white' }}"
                                data-locale="{{ $locale }}">
                            <x-icon category="Navigation" name="Globe" class="w-4 h-4"/>
                            <span>{{ strtoupper($locale) }}</span>
                            @if($locale === $default)
                                <span class="ml-1 text-emerald-300/80">{{ __('staff-lang-main-label') }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>

                <div class="space-y-3">
                    @foreach($locales as $locale)
                        <div class="lang-panel-category border border-white/15 rounded-2xl bg-black/40 p-4 {{ !$loop->first ? 'hidden' : '' }}"
                             data-locale="{{ $locale }}">

                            <div class="flex items-center justify-between mb-3">
                                <div class="text-[11px] uppercase tracking-[0.22em] text-white/40">
                                    {{ strtoupper($locale) }}
                                    @if($locale === $default)
                                        <span class="ml-2 text-emerald-300/80">
                                            {{ __('staff-lang-main-required') }}
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 text-white/30">
                                    <x-icon category="Interface" name="Edit" class="w-4 h-4"/>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="space-y-1.5">
                                    <label class="flex items-center gap-2 text-[11px] uppercase tracking-[0.18em] text-white/60">
                                        <x-icon category="Edit" name="Text" class="w-4 h-4"/>
                                        <span>{{ __('staff-field-name-label') }}</span>
                                    </label>
                                    <input type="text"
                                           name="name[{{ $locale }}]"
                                           value="{{ old("name.$locale") }}"
                                           class="w-full bg-black/60 border border-white/20 rounded-2xl px-3.5 py-2.5 text-xs text-white/90 placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/30">
                                </div>

                                <div class="space-y-1.5">
                                    <label class="flex items-center gap-2 text-[11px] uppercase tracking-[0.18em] text-white/60">
                                        <x-icon category="Edit" name="Text_Align_Left" class="w-4 h-4"/>
                                        <span>{{ __('staff-field-description-optional-label') }}</span>
                                    </label>
                                    <textarea name="description[{{ $locale }}]"
                                              rows="2"
                                              class="w-full bg-black/60 border border-white/20 rounded-2xl px-3.5 py-2.5 text-xs text-white/90 placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/30">{{ old("description.$locale") }}</textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @error('name')
                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                @enderror
                @error("name.$default")
                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                @enderror

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <label class="flex items-center gap-2 rounded-2xl border border-white/15 bg-black/40 px-3.5 py-2.5 text-xs text-white/85">
                        <input type="checkbox"
                               id="is_special"
                               name="is_special"
                               value="1"
                               class="w-4 h-4 rounded border-white/40 bg-black/60"
                               {{ old('is_special') ? 'checked' : '' }}>
                        <x-icon category="Interface" name="Star" class="w-4 h-4"/>
                        <span class="text-white/80">{{ __('staff-menu-category-special-label') }}</span>
                    </label>

                    <label class="flex items-center gap-2 rounded-2xl border border-white/15 bg-black/40 px-3.5 py-2.5 text-xs text-white/85">
                        <input type="checkbox"
                               id="is_available"
                               name="is_available"
                               value="1"
                               class="w-4 h-4 rounded border-white/40 bg-black/60"
                               {{ old('is_available', true) ? 'checked' : '' }}>
                        <x-icon category="Interface" name="Checkbox_Check" class="w-4 h-4"/>
                        <span class="text-white/80">{{ __('staff-menu-category-available-label') }}</span>
                    </label>
                </div>
            </div>

            <div class="px-5 md:px-6 py-4 border-t border-white/10 bg-black/30">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full border border-white/60 text-[11px] uppercase tracking-[0.18em] text-white hover:bg-white hover:text-black transition focus:outline-none focus:ring-2 focus:ring-white/30">
                    <x-icon category="System" name="Save" class="w-5 h-5"/>
                    <span>{{ __('staff-save-button') }}</span>
                </button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.lang-tab-category');
    const panels = document.querySelectorAll('.lang-panel-category');

            tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const locale = tab.dataset.locale;

            tabs.forEach(t => {
                t.classList.remove('bg-red-600/80', 'text-white', 'border-red-400', 'shadow-[0_0_10px_rgba(248,113,113,0.45)]');
                t.classList.add('bg-black/40', 'text-white/60', 'border-white/30');
            });
            tab.classList.add('bg-red-600/80', 'text-white', 'border-red-400', 'shadow-[0_0_10px_rgba(248,113,113,0.45)]');
            tab.classList.remove('bg-black/40', 'text-white/60', 'border-white/30');

            panels.forEach(panel => {
                panel.classList.toggle('hidden', panel.dataset.locale !== locale);
            });
        });
    });
});
</script>
@endpush
