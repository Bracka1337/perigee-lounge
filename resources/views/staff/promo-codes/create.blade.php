@extends('layouts.staff')

@section('content')
    <div class="mb-8">
        <h1 class="logo-font text-3xl md:text-4xl mb-2 drop-shadow-[0_0_10px_rgba(255,255,255,0.7)]">
            {{ __('staff-promo-create-title') }}
        </h1>
        <p class="text-sm text-white/70">
            {{ __('staff-promo-create-subtitle') }}
        </p>
    </div>

    @if($errors->any())
        <div class="mb-5 text-xs text-red-200 border border-red-500/50 bg-red-500/10 px-4 py-3 rounded-2xl">
            <div class="flex items-start gap-2">
                <x-icon category="Warning" name="Info" class="w-4 h-4 mt-0.5"/>
                <div>{{ __('staff-promo-validation-error') }}</div>
            </div>
        </div>
    @endif

    <form method="POST"
          action="{{ route('staff.promo-codes.store', ['locale' => app()->getLocale()]) }}"
          class="max-w-3xl text-xs">
        @csrf

        <div class="rounded-3xl border border-white/15 bg-black/40 backdrop-blur-xl shadow-[0_0_30px_rgba(0,0,0,0.35)] overflow-hidden">
            <div class="p-5 md:p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2 space-y-1.5">
                        <label class="flex items-center gap-2 text-[11px] uppercase tracking-[0.22em] text-white/60">
                            <x-icon category="Interface" name="Ticket_Voucher" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-public-code') }}</span>
                        </label>
                        <input type="text"
                               name="public_code"
                               value="{{ old('public_code') }}"
                               class="w-full bg-black/60 border border-white/20 rounded-2xl px-3.5 py-2.5 text-xs text-white/90 placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/30">
                    </div>

                    <div class="md:col-span-2 space-y-1.5">
                        <label class="flex items-center gap-2 text-[11px] uppercase tracking-[0.22em] text-white/60">
                            <x-icon category="Edit" name="Text" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-description') }}</span>
                        </label>
                        <textarea name="description"
                                  rows="3"
                                  class="w-full bg-black/60 border border-white/20 rounded-2xl px-3.5 py-2.5 text-xs text-white/90 placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/30">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="h-px bg-white/10"></div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="flex items-center gap-2 text-[11px] uppercase tracking-[0.22em] text-white/60">
                            <x-icon category="Interface" name="Settings" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-discount-type') }}</span>
                        </label>
                        <select name="discount_type"
                                class="w-full bg-black/60 border border-white/20 rounded-2xl px-3.5 py-2.5 text-xs text-white/90 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/30">
                            <option value="percent" @selected(old('discount_type') === 'percent')>
                                {{ __('staff-promo-field-discount-type-percent') }}
                            </option>
                            <option value="fixed" @selected(old('discount_type') === 'fixed')>
                                {{ __('staff-promo-field-discount-type-fixed') }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="flex items-center gap-2 text-[11px] uppercase tracking-[0.22em] text-white/60">
                            <x-icon category="Interface" name="Tag" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-discount-value') }}</span>
                        </label>
                        <input type="number"
                               name="discount_value"
                               step="0.01"
                               min="0"
                               value="{{ old('discount_value') }}"
                               class="w-full bg-black/60 border border-white/20 rounded-2xl px-3.5 py-2.5 text-xs text-white/90 placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/30">
                    </div>

                    <div class="space-y-1.5">
                        <label class="flex items-center gap-2 text-[11px] uppercase tracking-[0.22em] text-white/60">
                            <x-icon category="User" name="Users" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-max-uses') }}</span>
                        </label>
                        <input type="number"
                               name="max_uses"
                               min="1"
                               value="{{ old('max_uses') }}"
                               class="w-full bg-black/60 border border-white/20 rounded-2xl px-3.5 py-2.5 text-xs text-white/90 placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/30"
                               placeholder="{{ __('staff-promo-field-max-uses-placeholder') }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="flex items-center gap-2 text-[11px] uppercase tracking-[0.22em] text-white/60">
                            <x-icon category="Calendar" name="Calendar" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-valid-from') }}</span>
                        </label>
                        <div class="inline-flex w-full items-center gap-2 rounded-2xl border border-white/20 bg-black/60 px-3.5 py-2.5 overflow-hidden">
                            <input id="promoValidFrom"
                                   type="datetime-local"
                                   name="valid_from"
                                   value="{{ old('valid_from') }}"
                                   class="w-full min-w-0 max-w-full bg-transparent border-0 p-0 text-xs text-white/90 focus:outline-none focus:ring-0 appearance-none"
                                   style="color-scheme: dark;">
                            <label for="promoValidFrom" class="shrink-0 text-white/70 cursor-pointer hover:text-white transition">
                                <x-icon category="Calendar" name="Calendar_Days" class="w-4 h-4"/>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="flex items-center gap-2 text-[11px] uppercase tracking-[0.22em] text-white/60">
                            <x-icon category="Calendar" name="Clock" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-valid-until') }}</span>
                        </label>
                        <div class="inline-flex w-full items-center gap-2 rounded-2xl border border-white/20 bg-black/60 px-3.5 py-2.5 overflow-hidden">
                            <input id="promoValidUntil"
                                   type="datetime-local"
                                   name="valid_until"
                                   value="{{ old('valid_until') }}"
                                   class="w-full min-w-0 max-w-full bg-transparent border-0 p-0 text-xs text-white/90 focus:outline-none focus:ring-0 appearance-none"
                                   style="color-scheme: dark;">
                            <label for="promoValidUntil" class="shrink-0 text-white/70 cursor-pointer hover:text-white transition">
                                <x-icon category="Calendar" name="Calendar_Days" class="w-4 h-4"/>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="flex items-center gap-2 text-[11px] uppercase tracking-[0.22em] text-white/60">
                            <x-icon category="Interface" name="Checkbox_Check" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-active') }}</span>
                        </label>
                        <label class="flex items-center gap-2 rounded-2xl border border-white/15 bg-black/40 px-3.5 py-2.5 text-xs text-white/85">
                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   @checked(old('is_active', true))
                                   class="rounded border-white/40 bg-black/60">
                            <span>{{ __('staff-promo-field-active-label') }}</span>
                        </label>
                    </div>
                </div>

                <div class="h-px bg-white/10"></div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="flex items-center gap-2 text-[11px] uppercase tracking-[0.22em] text-white/60">
                            <x-icon category="Navigation" name="Globe" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-scope') }}</span>
                        </label>
                        <select id="promo-scope"
                                name="scope"
                                class="w-full bg-black/60 border border-white/20 rounded-2xl px-3.5 py-2.5 text-xs text-white/90 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/30">
                            <option value="all" @selected(old('scope') === 'all')>
                                {{ __('staff-promo-field-scope-all') }}
                            </option>
                            <option value="category" @selected(old('scope') === 'category')>
                                {{ __('staff-promo-field-scope-category') }}
                            </option>
                            <option value="item" @selected(old('scope') === 'item')>
                                {{ __('staff-promo-field-scope-item') }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-1.5" id="promo-category-wrap">
                        <label class="flex items-center gap-2 text-[11px] uppercase tracking-[0.22em] text-white/60">
                            <x-icon category="File" name="Folder" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-category') }}</span>
                        </label>
                        <select id="promo-category"
                                name="menu_category_id"
                                class="w-full bg-black/60 border border-white/20 rounded-2xl px-3.5 py-2.5 text-xs text-white/90 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/30">
                            <option value="">{{ __('staff-promo-field-category-placeholder') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('menu_category_id') == $category->id)>
                                    {{ $category->name[app()->getLocale()] ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5" id="promo-item-wrap">
                        <label class="flex items-center gap-2 text-[11px] uppercase tracking-[0.22em] text-white/60">
                            <x-icon category="File" name="File_Document" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-item') }}</span>
                        </label>
                        <select id="promo-item"
                                name="menu_item_id"
                                class="w-full bg-black/60 border border-white/20 rounded-2xl px-3.5 py-2.5 text-xs text-white/90 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/30">
                            <option value="">{{ __('staff-promo-field-item-placeholder') }}</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" @selected(old('menu_item_id') == $item->id)>
                                    {{ $item->category->name[app()->getLocale()] ?? '' }} · {{ $item->name[app()->getLocale()] ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="px-5 md:px-6 py-4 border-t border-white/10 bg-black/30">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full border border-white/60 text-[11px] uppercase tracking-[0.22em] text-white hover:bg-white hover:text-black transition focus:outline-none focus:ring-2 focus:ring-white/30">
                    <x-icon category="System" name="Save" class="w-5 h-5"/>
                    <span>{{ __('staff-promo-submit') }}</span>
                </button>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const scope = document.getElementById('promo-scope')
                const catWrap = document.getElementById('promo-category-wrap')
                const itemWrap = document.getElementById('promo-item-wrap')
                const cat = document.getElementById('promo-category')
                const item = document.getElementById('promo-item')

                function setDisabled(el, disabled) {
                    if (!el) return
                    el.disabled = disabled
                    el.classList.toggle('opacity-50', disabled)
                    el.classList.toggle('pointer-events-none', disabled)
                }

                function sync() {
                    const v = scope ? scope.value : 'all'
                    const isCat = v === 'category'
                    const isItem = v === 'item'

                    setDisabled(catWrap, !isCat)
                    setDisabled(cat, !isCat)

                    setDisabled(itemWrap, !isItem)
                    setDisabled(item, !isItem)

                    if (!isCat && cat) cat.value = ''
                    if (!isItem && item) item.value = ''
                }

                if (scope) {
                    scope.addEventListener('change', sync)
                    sync()
                }
            })
        </script>
    @endpush
@endsection
