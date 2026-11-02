@extends('layouts.staff')

@section('content')
    <div class="mb-8">
        <h1 class="logo-font text-3xl md:text-4xl mb-2">
            {{ __('staff-promo-edit-title') }}
        </h1>
        <p class="text-sm text-ink-muted">
            {{ __('staff-promo-edit-subtitle') }}
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
          action="{{ route('staff.promo-codes.update', ['locale' => app()->getLocale(), 'promo_code' => $promoCode]) }}"
          class="max-w-3xl text-xs">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-line bg-ground/40 backdrop-blur-xl overflow-hidden">
            <div class="p-5 md:p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2 space-y-1.5">
                        <label class="flex items-center gap-2 text-xs text-ink-muted">
                            <x-icon category="Interface" name="Ticket_Voucher" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-public-code') }}</span>
                        </label>
                        <input type="text"
                               name="public_code"
                               value="{{ old('public_code', $promoCode->public_code) }}"
                               class="w-full bg-ground/60 border border-line rounded-2xl px-3.5 py-2.5 text-xs text-ink-soft placeholder:text-ink-muted focus:outline-none focus:ring-2 focus:ring-line-strong focus:border-line-strong">
                    </div>

                    <div class="md:col-span-2 space-y-1.5">
                        <label class="flex items-center gap-2 text-xs text-ink-muted">
                            <x-icon category="Edit" name="Text" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-description') }}</span>
                        </label>
                        <textarea name="description"
                                  rows="3"
                                  class="w-full bg-ground/60 border border-line rounded-2xl px-3.5 py-2.5 text-xs text-ink-soft placeholder:text-ink-muted focus:outline-none focus:ring-2 focus:ring-line-strong focus:border-line-strong">{{ old('description', $promoCode->description) }}</textarea>
                    </div>
                </div>

                <div class="h-px bg-line"></div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="flex items-center gap-2 text-xs text-ink-muted">
                            <x-icon category="Interface" name="Settings" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-discount-type') }}</span>
                        </label>
                        <select name="discount_type"
                                class="w-full bg-ground/60 border border-line rounded-2xl px-3.5 py-2.5 text-xs text-ink-soft focus:outline-none focus:ring-2 focus:ring-line-strong focus:border-line-strong">
                            <option value="percent" @selected(old('discount_type', $promoCode->discount_type) === 'percent')>
                                {{ __('staff-promo-field-discount-type-percent') }}
                            </option>
                            <option value="fixed" @selected(old('discount_type', $promoCode->discount_type) === 'fixed')>
                                {{ __('staff-promo-field-discount-type-fixed') }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="flex items-center gap-2 text-xs text-ink-muted">
                            <x-icon category="Interface" name="Tag" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-discount-value') }}</span>
                        </label>
                        <input type="number"
                               name="discount_value"
                               step="0.01"
                               min="0"
                               value="{{ old('discount_value', $promoCode->discount_value) }}"
                               class="w-full bg-ground/60 border border-line rounded-2xl px-3.5 py-2.5 text-xs text-ink-soft placeholder:text-ink-muted focus:outline-none focus:ring-2 focus:ring-line-strong focus:border-line-strong">
                    </div>

                    <div class="space-y-1.5">
                        <label class="flex items-center gap-2 text-xs text-ink-muted">
                            <x-icon category="User" name="Users" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-max-uses') }}</span>
                        </label>
                        <input type="number"
                               name="max_uses"
                               min="1"
                               value="{{ old('max_uses', $promoCode->max_uses) }}"
                               class="w-full bg-ground/60 border border-line rounded-2xl px-3.5 py-2.5 text-xs text-ink-soft placeholder:text-ink-muted focus:outline-none focus:ring-2 focus:ring-line-strong focus:border-line-strong"
                               placeholder="{{ __('staff-promo-field-max-uses-placeholder') }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="flex items-center gap-2 text-xs text-ink-muted">
                            <x-icon category="Calendar" name="Calendar" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-valid-from') }}</span>
                        </label>
                        <div class="inline-flex w-full items-center gap-2 rounded-2xl border border-line bg-ground/60 px-3.5 py-2.5 overflow-hidden">
                            <input id="promoValidFrom"
                                   type="datetime-local"
                                   name="valid_from"
                                   value="{{ old('valid_from', $promoCode->valid_from?->format('Y-m-d\TH:i')) }}"
                                   class="w-full min-w-0 max-w-full bg-transparent border-0 p-0 text-xs text-ink-soft focus:outline-none focus:ring-0 appearance-none"
                                   style="color-scheme: dark;">
                            <label for="promoValidFrom" class="shrink-0 text-ink-muted cursor-pointer hover:text-ink transition">
                                <x-icon category="Calendar" name="Calendar_Days" class="w-4 h-4"/>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="flex items-center gap-2 text-xs text-ink-muted">
                            <x-icon category="Calendar" name="Clock" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-valid-until') }}</span>
                        </label>
                        <div class="inline-flex w-full items-center gap-2 rounded-2xl border border-line bg-ground/60 px-3.5 py-2.5 overflow-hidden">
                            <input id="promoValidUntil"
                                   type="datetime-local"
                                   name="valid_until"
                                   value="{{ old('valid_until', $promoCode->valid_until?->format('Y-m-d\TH:i')) }}"
                                   class="w-full min-w-0 max-w-full bg-transparent border-0 p-0 text-xs text-ink-soft focus:outline-none focus:ring-0 appearance-none"
                                   style="color-scheme: dark;">
                            <label for="promoValidUntil" class="shrink-0 text-ink-muted cursor-pointer hover:text-ink transition">
                                <x-icon category="Calendar" name="Calendar_Days" class="w-4 h-4"/>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="flex items-center gap-2 text-xs text-ink-muted">
                            <x-icon category="Interface" name="Checkbox_Check" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-active') }}</span>
                        </label>
                        <label class="flex items-center gap-2 rounded-2xl border border-line bg-ground/40 px-3.5 py-2.5 text-xs text-ink-soft">
                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   @checked(old('is_active', $promoCode->is_active))
                                   class="rounded border-line-strong bg-ground/60">
                            <span>{{ __('staff-promo-field-active-label') }}</span>
                        </label>
                    </div>
                </div>

                <div class="h-px bg-line"></div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="flex items-center gap-2 text-xs text-ink-muted">
                            <x-icon category="Navigation" name="Globe" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-scope') }}</span>
                        </label>
                        <select id="promo-scope"
                                name="scope"
                                class="w-full bg-ground/60 border border-line rounded-2xl px-3.5 py-2.5 text-xs text-ink-soft focus:outline-none focus:ring-2 focus:ring-line-strong focus:border-line-strong">
                            <option value="all" @selected(old('scope', $promoCode->scope) === 'all')>
                                {{ __('staff-promo-field-scope-all') }}
                            </option>
                            <option value="category" @selected(old('scope', $promoCode->scope) === 'category')>
                                {{ __('staff-promo-field-scope-category') }}
                            </option>
                            <option value="item" @selected(old('scope', $promoCode->scope) === 'item')>
                                {{ __('staff-promo-field-scope-item') }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-1.5" id="promo-category-wrap">
                        <label class="flex items-center gap-2 text-xs text-ink-muted">
                            <x-icon category="File" name="Folder" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-category') }}</span>
                        </label>
                        <select id="promo-category"
                                name="menu_category_id"
                                class="w-full bg-ground/60 border border-line rounded-2xl px-3.5 py-2.5 text-xs text-ink-soft focus:outline-none focus:ring-2 focus:ring-line-strong focus:border-line-strong">
                            <option value="">{{ __('staff-promo-field-category-placeholder') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    @selected(old('menu_category_id', $promoCode->menu_category_id) == $category->id)>
                                    {{ $category->name[app()->getLocale()] ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5" id="promo-item-wrap">
                        <label class="flex items-center gap-2 text-xs text-ink-muted">
                            <x-icon category="File" name="File_Document" class="w-4 h-4"/>
                            <span>{{ __('staff-promo-field-item') }}</span>
                        </label>
                        <select id="promo-item"
                                name="menu_item_id"
                                class="w-full bg-ground/60 border border-line rounded-2xl px-3.5 py-2.5 text-xs text-ink-soft focus:outline-none focus:ring-2 focus:ring-line-strong focus:border-line-strong">
                            <option value="">{{ __('staff-promo-field-item-placeholder') }}</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}"
                                    @selected(old('menu_item_id', $promoCode->menu_item_id) == $item->id)>
                                    {{ $item->category->name[app()->getLocale()] ?? '' }} · {{ $item->name[app()->getLocale()] ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="px-5 md:px-6 py-4 border-t border-line bg-ground/30">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full border border-line-strong text-xs text-ink hover:bg-ink hover:text-on-nebula transition focus:outline-none focus:ring-2 focus:ring-line-strong">
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
