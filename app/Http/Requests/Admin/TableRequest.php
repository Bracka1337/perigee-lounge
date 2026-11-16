<?php

namespace App\Http\Requests\Admin;

use App\Models\Table;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class TableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string|Unique>>
     */
    public function rules(): array
    {
        $table = $this->route('table');
        $uniqueName = Rule::unique('tables', 'name');

        if ($table instanceof Table) {
            $uniqueName->ignore($table->id);
        }

        return [
            'name' => ['required', 'string', 'max:255', $uniqueName],
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'pos_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pos_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'width' => ['nullable', 'integer', 'min:10', 'max:1000'],
            'height' => ['nullable', 'integer', 'min:10', 'max:1000'],
            'rotation' => ['nullable', 'integer', 'min:0', 'max:359'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
