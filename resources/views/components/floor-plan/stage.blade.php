{{-- One floor of the plan, drawn to the building's proportions, with its rooms under the tables
     passed in the slot. It can be zoomed and dragged around (resources/js/floor-plan.js). --}}
@props(['floor', 'frame', 'active', 'roomBooking' => [], 'roomRenders' => []])

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
                @php($booking = $roomBooking[$room->id] ?? null)
                <div @class(['floor-room floor-room--label-only', 'is-booked' => $booking['booked'] ?? false])
                     style="{{ $frame->roomStyle($room) }} --room: {{ $room->color->hex() }};">
                    @if ($booking)
                        {{-- Rooms that can be booked as a whole: the name opens the room in the panel. --}}
                        <button type="button" class="floor-room__label floor-room__book js-room-book"
                                data-room-id="{{ $room->id }}"
                                data-name="{{ $room->t('name') }}"
                                data-floor="{{ $floor->t('name') }}"
                                data-seats="{{ $booking['seats'] }}"
                                data-taken="{{ $booking['taken'] ? '1' : '0' }}"
                                data-render="{{ $roomRenders[$room->id] ?? '' }}"
                                aria-label="{{ __('room-booking-choose', ['room' => $room->t('name')]) }}">
                            {{ $room->t('name') }} <span aria-hidden="true">◆</span>
                        </button>
                    @else
                        <span class="floor-room__label">{{ $room->t('name') }}</span>
                    @endif
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
