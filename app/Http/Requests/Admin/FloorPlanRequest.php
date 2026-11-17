<?php

namespace App\Http\Requests\Admin;

use App\Enums\DoorKind;
use App\Enums\FixtureType;
use App\Enums\RoomColor;
use App\Enums\RoomKind;
use App\Enums\RoomMood;
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

        // Doors and fixtures are optional: a plan without them leaves them as they are.
        foreach (['doors', 'fixtures'] as $key) {
            if (is_array($plan) && array_key_exists($key, $plan)) {
                $this->merge([$key => $plan[$key]]);
            }
        }
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
            'rooms.*' => ['array:id,name,kind,color,mood,is_bookable_whole,x,y,width,height'],
            'rooms.*.id' => ['nullable', 'integer', 'distinct', Rule::exists('rooms', 'id')->where('floor_id', $floorId)],
            'rooms.*.name' => ['required', 'array:en,lv,ru'],
            'rooms.*.name.en' => ['required', 'string', 'max:60', 'regex:/\S/'],
            'rooms.*.name.lv' => ['nullable', 'string', 'max:60'],
            'rooms.*.name.ru' => ['nullable', 'string', 'max:60'],
            'rooms.*.kind' => ['required', Rule::enum(RoomKind::class)],
            'rooms.*.color' => ['required', Rule::enum(RoomColor::class)],
            'rooms.*.is_bookable_whole' => ['sometimes', 'boolean'],
            'rooms.*.mood' => ['sometimes', Rule::enum(RoomMood::class)],
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

            'doors' => ['sometimes', 'array', 'max:120'],
            'doors.*' => ['array:id,kind,orientation,x,y,width'],
            'doors.*.id' => ['nullable', 'integer', 'distinct', Rule::exists('doors', 'id')->where('floor_id', $floorId)],
            'doors.*.kind' => ['required', Rule::enum(DoorKind::class)],
            'doors.*.orientation' => ['required', 'in:h,v'],
            'doors.*.x' => $coordinate,
            'doors.*.y' => $coordinate,
            'doors.*.width' => ['required', 'numeric', 'min:0.6', 'max:3'],

            'fixtures' => ['sometimes', 'array', 'max:200'],
            'fixtures.*' => ['array:id,type,x,y,rotation,width,depth,points,settings'],
            'fixtures.*.id' => ['nullable', 'integer', 'distinct', Rule::exists('fixtures', 'id')->where('floor_id', $floorId)],
            'fixtures.*.type' => ['required', Rule::enum(FixtureType::class)],
            'fixtures.*.x' => $coordinate,
            'fixtures.*.y' => $coordinate,
            'fixtures.*.rotation' => ['required', 'integer', 'min:0', 'max:359'],
            'fixtures.*.width' => ['required', 'numeric', 'min:0.1', 'max:20'],
            'fixtures.*.depth' => ['required', 'numeric', 'min:0.1', 'max:20'],
            'fixtures.*.points' => ['nullable', 'array', 'min:2', 'max:8'],
            'fixtures.*.points.*' => ['array:0,1', 'size:2'],
            'fixtures.*.points.*.*' => ['required', 'numeric', 'min:-'.self::MAX_COORDINATE, 'max:'.self::MAX_COORDINATE],
            'fixtures.*.settings' => ['nullable', 'array:color,strength'],
            'fixtures.*.settings.color' => ['sometimes', Rule::in(array_keys(FixtureType::LIGHT_COLORS))],
            'fixtures.*.settings.strength' => ['sometimes', 'integer', 'min:1', 'max:10'],
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

                // A door has to sit on the wall of a room, and a fixture (every corner of a bar
                // counter) inside one.
                foreach ($this->doors() ?? [] as $i => $door) {
                    $onWall = array_filter($rooms, fn (array $room) => $this->onWall($room, $door));
                    if ($onWall === []) {
                        $validator->errors()->add("doors.{$i}", (string) __('admin-plan-door-off-wall'));
                    }
                }

                foreach ($this->fixtures() ?? [] as $i => $fixture) {
                    $points = $fixture['points'] ?? null;
                    foreach ($points === null || $points === [] ? [[$fixture['x'], $fixture['y']]] : $points as $point) {
                        if (array_filter($rooms, fn (array $room) => $this->contains($room, (float) $point[0], (float) $point[1])) === []) {
                            $validator->errors()->add("fixtures.{$i}", (string) __('admin-plan-fixture-outside'));
                            break;
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
            'doors.*.width.min' => (string) __('admin-plan-door-width'),
            'doors.*.width.max' => (string) __('admin-plan-door-width'),
            'fixtures.*.points.min' => (string) __('admin-plan-bar-points'),
            'fixtures.*.points.max' => (string) __('admin-plan-bar-points'),
        ];
    }

    /**
     * @return array<int, array{id?: int|string|null, name: array<string, string|null>, kind: string, color: string, mood?: string, is_bookable_whole?: bool|int|string, x: int|float|string, y: int|float|string, width: int|float|string, height: int|float|string}>
     */
    public function rooms(): array
    {
        /** @var array<int, array{id?: int|string|null, name: array<string, string|null>, kind: string, color: string, mood?: string, is_bookable_whole?: bool|int|string, x: int|float|string, y: int|float|string, width: int|float|string, height: int|float|string}> $rooms */
        $rooms = $this->input('rooms', []);

        return $rooms;
    }

    /**
     * @return array<int, array{id?: int|string|null, name: string, capacity: int|string, x: int|float|string, y: int|float|string, width: int|string, height: int|string, rotation: int|string, is_active: bool|int|string}>
     */
    /**
     * @return array<int, array{id?: int|string|null, kind: string, orientation: string, x: int|float|string, y: int|float|string, width: int|float|string}>|null
     */
    public function doors(): ?array
    {
        if (! $this->has('doors')) {
            return null;
        }

        /** @var array<int, array{id?: int|string|null, kind: string, orientation: string, x: int|float|string, y: int|float|string, width: int|float|string}> $doors */
        $doors = $this->input('doors', []);

        return $doors;
    }

    /**
     * @return array<int, array{id?: int|string|null, type: string, x: int|float|string, y: int|float|string, rotation: int|string, width: int|float|string, depth: int|float|string, points?: list<array{0: int|float|string, 1: int|float|string}>|null, settings?: array{color?: string, strength?: int|string}|null}>|null
     */
    public function fixtures(): ?array
    {
        if (! $this->has('fixtures')) {
            return null;
        }

        /** @var array<int, array{id?: int|string|null, type: string, x: int|float|string, y: int|float|string, rotation: int|string, width: int|float|string, depth: int|float|string, points?: list<array{0: int|float|string, 1: int|float|string}>|null, settings?: array{color?: string, strength?: int|string}|null}> $fixtures */
        $fixtures = $this->input('fixtures', []);

        return $fixtures;
    }

    /**
     * Whether a door lies on one of the room's walls, within 15 cm, and fits along it.
     *
     * @param  array{x: int|float|string, y: int|float|string, width: int|float|string, height: int|float|string}  $room
     * @param  array{id?: int|string|null, kind: string, orientation: string, x: int|float|string, y: int|float|string, width: int|float|string}  $door
     */
    private function onWall(array $room, array $door): bool
    {
        $tolerance = 0.15;
        [$x, $y, $half] = [(float) $door['x'], (float) $door['y'], (float) $door['width'] / 2];
        [$left, $top] = [(float) $room['x'], (float) $room['y']];
        [$right, $bottom] = [$left + (float) $room['width'], $top + (float) $room['height']];

        if ($door['orientation'] === 'h') {
            return (abs($y - $top) <= $tolerance || abs($y - $bottom) <= $tolerance)
                && $x - $half >= $left - $tolerance && $x + $half <= $right + $tolerance;
        }

        return (abs($x - $left) <= $tolerance || abs($x - $right) <= $tolerance)
            && $y - $half >= $top - $tolerance && $y + $half <= $bottom + $tolerance;
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
