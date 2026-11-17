{{-- 2D / 3D switch. Hidden until lounge-toggle.js finds WebGL and a wide enough screen. --}}
@props(['tableSelector', 'input'])

<div data-view-toggle data-table-selector="{{ $tableSelector }}" data-input="{{ $input }}"
     class="mb-3 hidden rounded-full border border-line p-0.5" role="group" aria-label="{{ __('lounge-view-toggle-label') }}">
    <button type="button" data-view="2d" aria-pressed="true" title="{{ __('lounge-view-2d-help') }}"
            class="inline-flex min-h-11 items-center gap-2 rounded-full px-4 text-xs text-ink-muted transition hover:text-ink aria-pressed:bg-nebula aria-pressed:font-semibold aria-pressed:text-on-nebula">
        <svg viewBox="0 0 20 20" class="h-4 w-4" aria-hidden="true"><path d="M3 3h14v14H3zM3 10h14M10 3v14" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
        {{ __('lounge-view-2d') }}
    </button>
    <button type="button" data-view="3d" aria-pressed="false" title="{{ __('lounge-view-3d-help') }}"
            class="inline-flex min-h-11 items-center gap-2 rounded-full px-4 text-xs text-ink-muted transition hover:text-ink aria-pressed:bg-nebula aria-pressed:font-semibold aria-pressed:text-on-nebula">
        <svg viewBox="0 0 20 20" class="h-4 w-4" aria-hidden="true"><path d="M10 2l7 4v8l-7 4-7-4V6zM3 6l7 4 7-4M10 10v8" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
        {{ __('lounge-view-3d') }}
    </button>
</div>
