<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'table_id' => ['nullable', 'required_without:room_id', 'prohibits:room_id', 'integer', 'exists:tables,id'],
            'room_id' => ['nullable', 'required_without:table_id', 'integer', 'exists:rooms,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
            'mode' => ['required', 'in:phone,walkin'],
            'guest_name' => ['nullable', 'string', 'max:255'],
            'guest_phone' => ['nullable', 'string', 'max:255'],
            'guests_count' => ['required', 'integer', 'min:1', $this->filled('room_id') ? 'max:200' : 'max:20'],
        ];
    }
}
