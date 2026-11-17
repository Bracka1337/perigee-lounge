{{-- One floor of the plan, drawn to the building's proportions, with its rooms under the tables
     passed in the slot. It can be zoomed and dragged around (resources/js/floor-plan.js). --}}
@props(['floor', 'frame', 'active'])

<div data-floor-panel="{{ $floor->id }}" data-plan-viewport
     @class(['plan-viewport relative overflow-hidden rounded-2xl border border-line bg-ground/70 select-none', 'hidden' => $floor->id !== $active])>
    <div data-plan-stage class="plan-stage relative w-full" style="aspect-ratio: {{ $frame->aspectRatio() }};">
        @foreach ($floor->rooms as $room)
            <div class="floor-room floor-room--{{ $room->kind->value }}"
                 style="{{ $frame->roomStyle($room) }} --room: {{ $room->color->hex() }};"></div>
        @endforeach

        <x-floor-plan.furnishings :floor="$floor" :frame="$frame" />

        {{-- Room names go above the doors and fixtures, so nothing covers them. --}}
        @foreach ($floor->rooms as $room)
            @if ($room->kind !== \App\Enums\RoomKind::Corridor)
                <div class="floor-room floor-room--label-only" style="{{ $frame->roomStyle($room) }} --room: {{ $room->color->hex() }};">
                    <span class="floor-room__label">{{ $room->t('name') }}</span>
                </div>
            @endif
        @endforeach

        {{ $slot }}
    </div>

    <div class="absolute bottom-2 right-2 flex gap-1" role="group" aria-label="{{ __('floor-plan-zoom-label') }}">
        <button type="button" data-plan-zoom="in" class="plan-zoom-button" aria-label="{{ __('floor-plan-zoom-in') }}" title="{{ __('floor-plan-zoom-in') }}">+</button>
        <button type="button" data-plan-zoom="out" class="plan-zoom-button" aria-label="{{ __('floor-plan-zoom-out') }}" title="{{ __('floor-plan-zoom-out') }}">&minus;</button>
        <button type="button" data-plan-zoom="fit" class="plan-zoom-button px-3 text-xs" title="{{ __('floor-plan-zoom-fit-help') }}">{{ __('floor-plan-zoom-fit') }}</button>
    </div>
</div>
