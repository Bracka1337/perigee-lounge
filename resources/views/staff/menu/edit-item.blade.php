@extends('layouts.staff')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="logo-font text-2xl mb-1">
                {{ __('staff-menu-item-edit-title') }}
            </h1>
            <p class="text-sm text-ink-muted">
                {{ __('staff-menu-item-edit-subtitle') }}
            </p>
        </div>

        <a href="{{ route('staff.menu-categories.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-full border border-line bg-ground/30 text-xs text-ink-muted hover:text-ink hover:border-line transition">
            <x-icon category="Arrow" name="Arrow_Undo_Up_Left" class="w-5 h-5"/>
            <span>{{ __('staff-back-to-menu') }}</span>
        </a>
    </div>

    @php
        $locales = config('site.locales');
        $default = config('site.default_locale');
        $nameArr = $menuItem->name ?? [];
        $descArr = $menuItem->description ?? [];
        $allergensArr = $menuItem->allergens ?? [];
    @endphp

    <form method="POST"
          action="{{ route('staff.menu-items.update', $menuItem) }}"
          enctype="multipart/form-data"
          class="max-w-2xl text-xs">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-line bg-ground/40 backdrop-blur-xl overflow-hidden">
            <div class="p-5 md:p-6 space-y-6">

                <div class="space-y-1.5">
                    <label class="flex items-center gap-2 text-xs text-ink-muted">
                        <x-icon category="File" name="Folder" class="w-4 h-4"/>
                        <span>{{ __('staff-field-category-label') }}</span>
                    </label>

                    <div class="relative">
                        <select name="menu_category_id"
                                class="w-full bg-ground/60 border border-line rounded-2xl px-3.5 py-2.5 pr-10 text-xs text-ink-soft appearance-none
                                       focus:outline-none focus:ring-2 focus:ring-line-strong focus:border-line-strong">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    @selected(old('menu_category_id', $menuItem->menu_category_id) == $category->id)>
                                    {{ $category->t('name') ?? ($category->name[config('site.default_locale')] ?? $category->id) }}
                                </option>
                            @endforeach
                        </select>

                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-ink-muted">
                            <x-icon category="Arrow" name="Chevron_Down" class="w-4 h-4"/>
                        </span>
                    </div>

                    @error('menu_category_id')
                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                  <div class="flex flex-wrap gap-2" id="item-lang-tabs">
                    @foreach($locales as $locale)
                        <button type="button"
                            class="lang-tab-item inline-flex items-center gap-2 px-3 py-1.5 rounded-full border text-xs
                                {{ $loop->first ? 'bg-nebula text-on-nebula border-nebula' : 'bg-ground/40 text-on-nebula-muted border-line-strong hover:text-on-nebula' }}"
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
                        <div class="lang-panel-item border border-line rounded-2xl bg-ground/40 p-4 {{ !$loop->first ? 'hidden' : '' }}"
                             data-locale="{{ $locale }}">

                            <div class="flex items-center justify-between mb-3">
                                <div class="text-xs text-ink-muted">
                                    {{ strtoupper($locale) }}
                                    @if($locale === $default)
                                        <span class="ml-2 text-emerald-300/80">
                                            {{ __('staff-lang-main-required') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-ink-muted">
                                    <x-icon category="Interface" name="Edit" class="w-4 h-4"/>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="space-y-1.5">
                                    <label class="flex items-center gap-2 text-xs text-ink-muted">
                                        <x-icon category="Edit" name="Text" class="w-4 h-4"/>
                                        <span>{{ __('staff-field-name-label') }}</span>
                                    </label>
                                    <input type="text"
                                           name="name[{{ $locale }}]"
                                           value="{{ old("name.$locale", $nameArr[$locale] ?? '') }}"
                                           class="w-full bg-ground/60 border border-line rounded-2xl px-3.5 py-2.5 text-xs text-ink-soft placeholder:text-ink-muted focus:outline-none focus:ring-2 focus:ring-line-strong focus:border-line-strong">
                                </div>

                                <div class="space-y-1.5">
                                    <label class="flex items-center gap-2 text-xs text-ink-muted">
                                        <x-icon category="Edit" name="Text_Align_Left" class="w-4 h-4"/>
                                        <span>{{ __('staff-field-description-label') }}</span>
                                    </label>
                                    <textarea name="description[{{ $locale }}]"
                                              rows="2"
                                              class="w-full bg-ground/60 border border-line rounded-2xl px-3.5 py-2.5 text-xs text-ink-soft placeholder:text-ink-muted focus:outline-none focus:ring-2 focus:ring-line-strong focus:border-line-strong">{{ old("description.$locale", $descArr[$locale] ?? '') }}</textarea>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="flex items-center gap-2 text-xs text-ink-muted">
                                        <x-icon category="Warning" name="Info" class="w-4 h-4"/>
                                        <span>{{ __('staff-field-allergens-label') }}</span>
                                    </label>
                                    <textarea name="allergens[{{ $locale }}]"
                                              rows="4"
                                              class="w-full bg-ground/60 border border-line rounded-2xl px-3.5 py-2.5 text-xs text-ink-soft placeholder:text-ink-muted focus:outline-none focus:ring-2 focus:ring-line-strong focus:border-line-strong">{{ old("allergens.$locale", $allergensArr[$locale] ?? '') }}</textarea>
                                    <p class="text-xs text-ink-muted mt-1">
                                        {{ __('staff-field-allergens-help') }}
                                    </p>
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

                <div class="h-px bg-line"></div>

                <div class="space-y-1.5">
                    <label class="flex items-center gap-2 text-xs text-ink-muted">
                        <x-icon category="Interface" name="Tag" class="w-4 h-4"/>
                        <span>{{ __('staff-field-price-label') }}</span>
                    </label>
                    <input type="number"
                           step="0.01"
                           name="price"
                           value="{{ old('price', $menuItem->price) }}"
                           class="w-full bg-ground/60 border border-line rounded-2xl px-3.5 py-2.5 text-xs text-ink-soft placeholder:text-ink-muted focus:outline-none focus:ring-2 focus:ring-line-strong focus:border-line-strong">
                    @error('price')
                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <label class="flex items-center gap-2 rounded-2xl border border-line bg-ground/40 px-3.5 py-2.5 text-xs text-ink-soft">
                        <input type="checkbox"
                               name="is_available"
                               value="1"
                               class="w-4 h-4 rounded border-line-strong bg-ground/60"
                               {{ old('is_available', $menuItem->is_available) ? 'checked' : '' }}>
                        <x-icon category="Interface" name="Checkbox_Check" class="w-4 h-4"/>
                        <span class="text-ink-soft">{{ __('staff-menu-item-available-label') }}</span>
                    </label>
                </div>

                <div class="space-y-1.5">
                    <label class="flex items-center gap-2 text-xs text-ink-muted">
                        <x-icon category="File" name="Image" class="w-4 h-4"/>
                        <span>{{ __('staff-field-image-optional-label') }}</span>
                    </label>

                    @if($menuItem->image_path)
                        <div class="mb-2 flex items-center gap-3 rounded-2xl border border-line bg-ground/30 p-3">
                            <img src="{{ asset('storage/'.$menuItem->image_path) }}"
                                 alt="{{ $menuItem->t('name') ?? ($nameArr[$default] ?? '') }}"
                                 class="w-14 h-14 rounded-xl object-cover">
                            <div class="text-xs text-ink-muted">
                                <div class="flex items-center gap-2">
                                    <x-icon category="File" name="Image" class="w-4 h-4"/>
                                    <span>{{ __('staff-field-current-image-label') }}</span>
                                </div>
                                <div class="text-ink-muted mt-0.5">{{ __('staff-field-image-keep-help') }}</div>
                            </div>
                        </div>
                    @endif

                    <div class="w-full bg-ground/60 border border-line rounded-2xl px-3.5 py-2.5">
                        <input type="file"
                               name="image"
                               class="w-full text-xs file:mr-3 file:px-3 file:py-1.5 file:rounded-full file:border file:border-line-strong file:bg-line file:text-ink file:text-xs file: file: hover:file:bg-line">
                    </div>
                    @error('image')
                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror

                    @if(!$menuItem->image_path)
                        <p class="text-xs text-ink-muted mt-1">
                            {{ __('staff-field-image-keep-help') }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="px-5 md:px-6 py-4 border-t border-line bg-ground/30">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full border border-line-strong text-xs text-ink hover:bg-ink hover:text-on-nebula transition focus:outline-none focus:ring-2 focus:ring-line-strong">
                    <x-icon category="System" name="Save" class="w-5 h-5"/>
                    <span>{{ __('staff-save-changes-button') }}</span>
                </button>
            </div>
        </div>
    </form>
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
