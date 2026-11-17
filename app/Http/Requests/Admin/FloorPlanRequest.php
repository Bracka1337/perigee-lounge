<?php

namespace App\Http\Requests\Admin;

use App\Enums\RoomColor;
use App\Enums\RoomKind;
use App\Models\Floor;
use App\Models\Table;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * The floor plan editor posts the rooms and tables of one floor as JSON in `plan_payload`.
 * Everything is in metres, except table footprints, which are in centimetres.
 */
class FloorPlanRequest extends FormRequest
{
    public const MIN_ROOM = 1;

    public const MAX_COORDINATE = 500;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $payload = $this->input('plan_payload');
        $plan = is_string($payload) ? json_decode($payload, true) : null;

        $this->merge([
            'rooms' => is_array($plan) && is_array($plan['rooms'] ?? null) ? $plan['rooms'] : null,
            'tables' => is_array($plan) && is_array($plan['tables'] ?? null) ? $plan['tables'] : null,
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $floor = $this->route('floor');
        $floorId = $floor instanceof Floor ? $floor->id : 0;
        $coordinate = ['required', 'numeric', 'min:-'.self::MAX_COORDINATE, 'max:'.self::MAX_COORDINATE];

        return [
            'rooms' => ['present', 'array', 'max:80'],
            'rooms.*' => ['array:id,name,kind,color,is_bookable_whole,x,y,width,height'],
            'rooms.*.id' => ['nullable', 'integer', 'distinct', Rule::exists('rooms', 'id')->where('floor_id', $floorId)],
            'rooms.*.name' => ['required', 'array:en,lv,ru'],
            'rooms.*.name.en' => ['required', 'string', 'max:60', 'regex:/\S/'],
            'rooms.*.name.lv' => ['nullable', 'string', 'max:60'],
            'rooms.*.name.ru' => ['nullable', 'string', 'max:60'],
            'rooms.*.kind' => ['required', Rule::enum(RoomKind::class)],
            'rooms.*.color' => ['required', Rule::enum(RoomColor::class)],
            'rooms.*.is_bookable_whole' => ['sometimes', 'boolean'],
            'rooms.*.x' => $coordinate,
            'rooms.*.y' => $coordinate,
            'rooms.*.width' => ['required', 'numeric', 'min:'.self::MIN_ROOM, 'max:'.self::MAX_COORDINATE],
            'rooms.*.height' => ['required', 'numeric', 'min:'.self::MIN_ROOM, 'max:'.self::MAX_COORDINATE],

            'tables' => ['present', 'array', 'max:200'],
            'tables.*' => ['array:id,name,capacity,x,y,width,height,rotation,is_active'],
            'tables.*.id' => ['nullable', 'integer', 'distinct', 'exists:tables,id'],
            'tables.*.name' => ['required', 'string', 'max:20', 'regex:/\S/', 'distinct:ignore_case'],
            'tables.*.capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'tables.*.x' => $coordinate,
            'tables.*.y' => $coordinate,
            'tables.*.width' => ['required', 'integer', 'min:40', 'max:2000'],
            'tables.*.height' => ['required', 'integer', 'min:40', 'max:2000'],
            'tables.*.rotation' => ['required', 'integer', 'min:0', 'max:359'],
            'tables.*.is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Rooms may touch but not overlap, every table stands inside a room, and table names are
     * unique across the whole lounge.
     *
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $rooms = $this->rooms();
                foreach ($rooms as $i => $a) {
                    foreach (array_slice($rooms, $i + 1, null, true) as $j => $b) {
                        if ($this->overlap($a, $b)) {
                            $validator->errors()->add("rooms.{$j}", (string) __('admin-plan-rooms-overlap', [
                                'first' => $a['name']['en'], 'second' => $b['name']['en'],
                            ]));
                        }
                    }
                }

                foreach ($this->tables() as $i => $table) {
                    $inside = array_filter($rooms, fn (array $room) => $this->contains($room, (float) $table['x'], (float) $table['y']));
                    if ($inside === []) {
                        $validator->errors()->add("tables.{$i}", (string) __('admin-plan-table-outside', ['name' => $table['name']]));
                    }
                }

                $ids = array_values(array_filter(array_map(fn (array $table) => $table['id'] ?? null, $this->tables()), fn ($id) => $id !== null));
                $names = array_map(fn (array $table) => mb_strtolower(trim($table['name'])), $this->tables());
                $taken = Table::query()->whereNotIn('id', $ids)->pluck('name')->map(fn ($name) => mb_strtolower((string) $name))->all();

                // A floor only saves its own tables (and tables that have no room yet), and it has to
                // send all of them, so none can be left behind in a room that moved.
                $floor = $this->route('floor');
                $roomIds = $floor instanceof Floor ? $floor->rooms()->pluck('id') : collect();
                $own = Table::query()->whereIn('room_id', $roomIds)->pluck('id')->all();
                $unplaced = Table::query()->whereNull('room_id')->pluck('id')->all();

                foreach ($this->tables() as $i => $table) {
                    if (isset($table['id']) && ! in_array((int) $table['id'], [...$own, ...$unplaced], true)) {
                        $validator->errors()->add("tables.{$i}.id", (string) __('admin-plan-table-other-floor', ['name' => $table['name']]));
                    }
                }

                if (array_diff($own, array_map('intval', $ids)) !== []) {
                    $validator->errors()->add('tables', (string) __('admin-plan-out-of-date'));
                }

                foreach (array_intersect($names, $taken) as $i => $name) {
                    $validator->errors()->add("tables.{$i}.name", (string) __('admin-plan-table-name-taken', ['name' => $this->tables()[$i]['name']]));
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $invalid = (string) __('admin-plan-invalid');

        return [
            'rooms.present' => $invalid,
            'rooms.array' => $invalid,
            'tables.present' => $invalid,
            'tables.array' => $invalid,
            'rooms.*.name.en.required' => (string) __('admin-rooms-name-required'),
            'rooms.*.name.en.regex' => (string) __('admin-rooms-name-required'),
            'rooms.*.width.min' => (string) __('admin-plan-room-too-small'),
            'rooms.*.height.min' => (string) __('admin-plan-room-too-small'),
            'tables.*.name.required' => (string) __('admin-plan-table-name-required'),
            'tables.*.name.regex' => (string) __('admin-plan-table-name-required'),
            'tables.*.name.distinct' => (string) __('admin-plan-table-name-twice'),
        ];
    }

    /**
     * @return array<int, array{id?: int|string|null, name: array<string, string|null>, kind: string, color: string, is_bookable_whole?: bool|int|string, x: int|float|string, y: int|float|string, width: int|float|string, height: int|float|string}>
     */
    public function rooms(): array
    {
        /** @var array<int, array{id?: int|string|null, name: array<string, string|null>, kind: string, color: string, is_bookable_whole?: bool|int|string, x: int|float|string, y: int|float|string, width: int|float|string, height: int|float|string}> $rooms */
        $rooms = $this->input('rooms', []);

        return $rooms;
    }

    /**
     * @return array<int, array{id?: int|string|null, name: string, capacity: int|string, x: int|float|string, y: int|float|string, width: int|string, height: int|string, rotation: int|string, is_active: bool|int|string}>
     */
    public function tables(): array
    {
        /** @var array<int, array{id?: int|string|null, name: string, capacity: int|string, x: int|float|string, y: int|float|string, width: int|string, height: int|string, rotation: int|string, is_active: bool|int|string}> $tables */
        $tables = $this->input('tables', []);

        return $tables;
    }

    /**
     * @param  array{x: int|float|string, y: int|float|string, width: int|float|string, height: int|float|string}  $a
     * @param  array{x: int|float|string, y: int|float|string, width: int|float|string, height: int|float|string}  $b
     */
    private function overlap(array $a, array $b): bool
    {
        $tolerance = 0.01;

        return (float) $a['x'] < (float) $b['x'] + (float) $b['width'] - $tolerance
            && (float) $b['x'] < (float) $a['x'] + (float) $a['width'] - $tolerance
            && (float) $a['y'] < (float) $b['y'] + (float) $b['height'] - $tolerance
            && (float) $b['y'] < (float) $a['y'] + (float) $a['height'] - $tolerance;
    }

    /**
     * @param  array{x: int|float|string, y: int|float|string, width: int|float|string, height: int|float|string}  $room
     */
    private function contains(array $room, float $x, float $y): bool
    {
        return $x >= (float) $room['x'] && $x <= (float) $room['x'] + (float) $room['width']
            && $y >= (float) $room['y'] && $y <= (float) $room['y'] + (float) $room['height'];
    }
}
