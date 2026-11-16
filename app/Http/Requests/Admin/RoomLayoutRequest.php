<?php

namespace App\Http\Requests\Admin;

use App\Enums\RoomColor;
use App\Enums\RoomKind;
use App\Models\Floor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Validator;

/**
 * The plan editor posts all rooms of one floor as a JSON list in `rooms_payload`.
 */
class RoomLayoutRequest extends FormRequest
{
    public const MIN_SIZE = 4;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $payload = $this->input('rooms_payload');
        $rooms = is_string($payload) ? json_decode($payload, true) : null;

        $this->merge(['rooms' => is_array($rooms) ? $rooms : null]);
    }

    /**
     * @return array<string, list<string|ValidationRule|Exists|Enum>>
     */
    public function rules(): array
    {
        $floor = $this->route('floor');
        $floorId = $floor instanceof Floor ? $floor->id : 0;

        return [
            'rooms' => ['present', 'array', 'max:60'],
            'rooms.*' => ['array:id,name,kind,color,x,y,width,height'],
            'rooms.*.id' => ['nullable', 'integer', Rule::exists('rooms', 'id')->where('floor_id', $floorId)],
            'rooms.*.name' => ['required', 'array:en,lv,ru'],
            'rooms.*.name.en' => ['required', 'string', 'max:60'],
            'rooms.*.name.lv' => ['nullable', 'string', 'max:60'],
            'rooms.*.name.ru' => ['nullable', 'string', 'max:60'],
            'rooms.*.kind' => ['required', Rule::enum(RoomKind::class)],
            'rooms.*.color' => ['required', Rule::enum(RoomColor::class)],
            'rooms.*.x' => ['required', 'numeric', 'min:0', 'max:100'],
            'rooms.*.y' => ['required', 'numeric', 'min:0', 'max:100'],
            'rooms.*.width' => ['required', 'numeric', 'min:'.self::MIN_SIZE, 'max:100'],
            'rooms.*.height' => ['required', 'numeric', 'min:'.self::MIN_SIZE, 'max:100'],
        ];
    }

    /**
     * A room has to lie fully inside the plan.
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

                /** @var array<int, array{x: int|float|string, y: int|float|string, width: int|float|string, height: int|float|string}> $rooms */
                $rooms = $this->input('rooms', []);

                foreach ($rooms as $index => $room) {
                    $right = (float) $room['x'] + (float) $room['width'];
                    $bottom = (float) $room['y'] + (float) $room['height'];

                    if ($right > 100.01 || $bottom > 100.01) {
                        $validator->errors()->add("rooms.{$index}.width", (string) __('admin-rooms-out-of-bounds'));
                    }
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $bounds = (string) __('admin-rooms-out-of-bounds');
        $messages = [
            'rooms.present' => (string) __('admin-rooms-invalid'),
            'rooms.array' => (string) __('admin-rooms-invalid'),
            'rooms.*.name.required' => (string) __('admin-rooms-name-required'),
            'rooms.*.name.en.required' => (string) __('admin-rooms-name-required'),
        ];

        foreach (['x', 'y', 'width', 'height'] as $field) {
            foreach (['required', 'numeric', 'min', 'max'] as $rule) {
                $messages["rooms.*.{$field}.{$rule}"] = $bounds;
            }
        }

        return $messages;
    }

    /**
     * @return list<array{id?: int|string|null, name: array<string, string|null>, kind: string, color: string, x: int|float|string, y: int|float|string, width: int|float|string, height: int|float|string}>
     */
    public function rooms(): array
    {
        /** @var list<array{id?: int|string|null, name: array<string, string|null>, kind: string, color: string, x: int|float|string, y: int|float|string, width: int|float|string, height: int|float|string}> $rooms */
        $rooms = array_values($this->validated('rooms', []));

        return $rooms;
    }
}
