@extends('layouts.admin')

@section('content')
    <div class="mb-8">
        <h1 class="logo-font text-3xl md:text-4xl mb-2 drop-shadow-[0_0_10px_rgba(255,255,255,0.7)]">
            {{ __('admin-translations-edit-title') }}
        </h1>
        <p class="text-sm text-white/70">
            {{ __('admin-translations-edit-subtitle', ['key' => $translation->key]) }}
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-6 bg-red-950/60 border border-red-500/60 text-red-100 text-sm rounded-xl px-4 py-3">
            <p class="font-semibold mb-2 tracking-wide">
                {{ __('admin-translations-form-errors-title') }}
            </p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li class="text-[13px]">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php($values = $translation->value ?? [])

    <div class="bg-white/5 border border-white/10 rounded-2xl px-6 py-6 md:px-8 md:py-7 max-w-3xl">
        <form action="{{ route('admin.translations.update', $translation) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="space-y-1.5">
                <label for="key" class="block text-xs uppercase tracking-[0.25em] text-white/60">
                    {{ __('admin-translations-field-key') }}
                </label>
                <input
                    type="text"
                    id="key"
                    name="key"
                    value="{{ old('key', $translation->key) }}"
                    class="w-full bg-black/60 border border-white/20 rounded-xl px-3 py-2.5 text-sm text-white
                           focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/70"
                    required
                >
            </div>

            <div class="flex flex-wrap gap-2 mb-2" id="tr-lang-tabs">
                @foreach($locales as $locale)
                    <button type="button"
                            class="lang-tab-item px-3 py-1.5 rounded-full border text-[11px] uppercase tracking-[0.18em]
                                   {{ $loop->first ? 'bg-red-600/80 text-white border-red-400 shadow-[0_0_10px_rgba(248,113,113,0.45)]' : 'bg-black/40 text-white/60 border-white/30 hover:text-white' }}"
                            data-locale="{{ $locale }}">
                        {{ strtoupper($locale) }}
                        @if($locale === $default)
                            <span class="ml-1 text-emerald-300/80">{{ __('admin-translations-lang-main-label') }}</span>
                        @endif
                    </button>
                @endforeach
            </div>

            <div class="space-y-3">
                @foreach($locales as $locale)
                    <div class="lang-panel-item border border-white/15 rounded-2xl bg-black/40 p-4 {{ !$loop->first ? 'hidden' : '' }}"
                         data-locale="{{ $locale }}">
                        <div class="text-[11px] uppercase tracking-[0.22em] text-white/40 mb-3">
                            {{ strtoupper($locale) }}
                            @if($locale === $default)
                                <span class="ml-2 text-emerald-300/80">
                                    {{ __('admin-translations-lang-main-required') }}
                                </span>
                            @endif
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs mb-1">
                                    {{ __('admin-translations-field-text') }}
                                </label>
                                <textarea
                                    name="value[{{ $locale }}]"
                                    rows="3"
                                    class="w-full bg-black/60 border border-white/20 rounded-xl px-3 py-2 text-sm text-white
                                           focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/70">{{ old("value.$locale", $values[$locale] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @error("value.$default")
                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
            @enderror

            <div class="pt-2 flex flex-wrap items-center gap-3">
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full
                               border border-red-500/80 bg-red-600/80
                               text-[11px] tracking-[0.25em] uppercase text-white
                               shadow-[0_0_14px_rgba(248,113,113,0.7)]
                               hover:bg-red-500 transition">
                    <x-icon category="Interface" name="Save" class="w-5 h-5 shrink-0"/>
                    <span>{{ __('admin-translations-save-button') }}</span>
                </button>

                <a href="{{ route('admin.translations.index') }}"
                   class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full
                          border border-white/30 text-[11px] tracking-[0.25em] uppercase text-white/70
                          hover:text-white hover:border-white/70 hover:bg-white/5 transition">
                    {{-- иконка отмены (гарантированно видимая) --}}
                    <svg class="w-5 h-5 shrink-0" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M6 6l8 8M14 6l-8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    <span>{{ __('admin-translations-cancel-button') }}</span>
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabs = document.querySelectorAll('.lang-tab-item');
            const panels = document.querySelectorAll('.lang-panel-item');

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
