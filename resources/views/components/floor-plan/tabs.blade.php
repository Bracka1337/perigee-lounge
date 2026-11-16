{{-- Floor switcher for the lounge plan. Hidden while the lounge has a single floor. --}}
@props(['floors', 'active'])

@if ($floors->count() > 1)
    <div class="mb-3 flex flex-wrap gap-2" role="tablist" aria-label="{{ __('floor-plan-floors') }}">
        @foreach ($floors as $floor)
            <button type="button" role="tab"
                    data-floor-tab="{{ $floor->id }}"
                    aria-selected="{{ $floor->id === $active ? 'true' : 'false' }}"
                    class="min-h-11 rounded-full border border-line px-4 text-xs text-ink-muted transition hover:text-ink
                           aria-selected:border-nebula aria-selected:bg-nebula/10 aria-selected:text-ink">
                {{ $floor->t('name') }}
            </button>
        @endforeach
    </div>
@endif
