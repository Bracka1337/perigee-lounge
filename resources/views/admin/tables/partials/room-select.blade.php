{{-- Room picker for the table form. Rooms are grouped by floor. --}}
<div class="space-y-1.5">
    <label for="room_id" class="block text-xs text-ink-muted">
        {{ __('admin-tables-field-room') }}
    </label>
    <select id="room_id" name="room_id"
            class="w-full bg-ground/60 border border-line rounded-xl px-3 py-2.5 text-sm text-ink focus:outline-none focus:border-nebula focus:ring-1 focus:ring-nebula/70">
        <option value="">{{ __('admin-tables-room-none') }}</option>
        @foreach ($floors as $floor)
            <optgroup label="{{ $floor->t('name') }}">
                @foreach ($floor->rooms as $room)
                    <option value="{{ $room->id }}" @selected((string) $selected === (string) $room->id)>
                        {{ $room->t('name') }}
                    </option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
</div>
