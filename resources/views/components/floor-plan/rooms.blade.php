{{-- The rooms of one floor, drawn under the tables. Sizes are percentages of the plan. --}}
@props(['floor'])

@foreach ($floor->rooms as $room)
    <div class="floor-room floor-room--{{ $room->kind->value }}"
         style="left: {{ $room->x }}%; top: {{ $room->y }}%; width: {{ $room->width }}%; height: {{ $room->height }}%; --room: {{ $room->color->hex() }};">
        <span class="floor-room__label">{{ $room->t('name') }}</span>
    </div>
@endforeach
