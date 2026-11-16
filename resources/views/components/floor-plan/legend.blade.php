{{-- Room legend per floor: the rooms that have tables to pick. --}}
@props(['floors', 'active'])

@foreach ($floors as $floor)
    @php($rooms = $floor->rooms->filter(fn ($room) => $floor->tables->contains('room_id', $room->id)))

    @if ($rooms->isNotEmpty())
        <div data-floor-panel="{{ $floor->id }}" @class(['hidden' => $floor->id !== $active])>
            <div class="mt-3 flex flex-col gap-2 border-t border-line pt-3 text-xs text-ink-muted sm:flex-row sm:flex-wrap sm:gap-3">
                @foreach ($rooms as $room)
                    <div class="flex items-center gap-2">
                        <span class="floor-room-swatch" style="--room: {{ $room->color->hex() }};"></span>
                        <span>{{ $room->t('name') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endforeach
