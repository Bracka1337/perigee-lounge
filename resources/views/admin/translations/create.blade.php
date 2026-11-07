@extends('layouts.admin')

@section('content')
    <div class="mb-8">
        <h1 class="logo-font text-3xl md:text-4xl mb-2">
            {{ __('admin-translations-create-title') }}
        </h1>
        <p class="text-sm text-ink-muted">
            {{ __('admin-translations-create-subtitle') }}
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-6 bg-red-950/60 border border-red-500/60 text-red-100 text-sm rounded-xl px-4 py-3">
            <p class="font-semibold mb-2 tracking-wide">
                {{ __('admin-translations-form-errors-title') }}
            </p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li class="text-sm">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-surface border border-line rounded-2xl px-6 py-6 md:px-8 md:py-7 max-w-3xl">
        <form action="{{ route('admin.translations.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="space-y-1.5">
                <label for="key" class="block text-xs text-ink-muted">
                    {{ __('admin-translations-field-key') }}
                </label>
                <input
                    type="text"
                    id="key"
                    name="key"
                    value="{{ old('key') }}"
                    class="w-full bg-ground/60 border border-line rounded-xl px-3 py-2.5 text-sm text-ink
                           focus:outline-none focus:border-nebula focus:ring-1 focus:ring-nebula/70"
                    placeholder="{{ __('admin-translations-field-key-placeholder') }}"
                    required
                >
            </div>

            <div class="flex flex-wrap gap-2 mb-2" id="tr-lang-tabs">
                @foreach($locales as $locale)
                    <button type="button"
                            class="lang-tab-item inline-flex items-center gap-2 px-3 py-1.5 rounded-full border text-xs
                                   {{ $loop->first ? 'bg-nebula text-on-nebula border-nebula' : 'bg-ground/40 text-on-nebula-muted border-line-strong hover:text-on-nebula' }}"
                            data-locale="{{ $locale }}">
                        <span>{{ strtoupper($locale) }}</span>

                        @if($locale === $default)
                            <span class="text-xs opacity-80">
                                {{ __('admin-translations-lang-main-label') }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>

            <div class="space-y-3">
                @foreach($locales as $locale)
                    <div class="lang-panel-item border border-line rounded-2xl bg-ground/40 p-4 {{ !$loop->first ? 'hidden' : '' }}"
                         data-locale="{{ $locale }}">
                        <div class="text-xs text-ink-muted mb-3">
                            {{ strtoupper($locale) }}
                            @if($locale === $default)
                                <span class="ml-2 text-emerald-300/80">
                                    {{ __('admin-translations-lang-main-required') }}
                                </span>
                            @endif
                        </div>

                        <div>
                            <label class="block text-xs mb-1 text-ink-muted">
                                {{ __('admin-translations-field-text') }}
                            </label>
                            <textarea
                                name="value[{{ $locale }}]"
                                rows="3"
                                class="w-full bg-ground/60 border border-line rounded-xl px-3 py-2 text-sm text-ink
                                       focus:outline-none focus:border-nebula focus:ring-1 focus:ring-nebula/70"
                            >{{ old("value.$locale") }}</textarea>
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
                               border border-nebula bg-nebula
                               text-xs font-semibold text-on-nebula
                               hover:border-ink hover:bg-ink transition">
                    <x-icon category="Interface" name="Save" class="w-5 h-5 shrink-0"/>
                    <span>{{ __('admin-translations-create-submit') }}</span>
                </button>

<a href="{{ route('admin.translations.index') }}"
   class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full
          border border-line-strong text-xs text-ink-muted
          hover:text-ink hover:border-line-strong hover:bg-surface transition">
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
                        t.classList.remove('bg-nebula', 'text-on-nebula', 'border-nebula', '');
                        t.classList.add('bg-ground/40', 'text-ink-muted', 'border-line-strong');
                    });

                    tab.classList.add('bg-nebula', 'text-on-nebula', 'border-nebula', '');
                    tab.classList.remove('bg-ground/40', 'text-ink-muted', 'border-line-strong');

                    panels.forEach(panel => {
                        panel.classList.toggle('hidden', panel.dataset.locale !== locale);
                    });
                });
            });
        });
    </script>
@endpush
