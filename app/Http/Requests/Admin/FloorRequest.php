<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FloorRequest extends FormRequest
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
            'name' => ['required', 'array:en,lv,ru'],
            'name.en' => ['required', 'string', 'max:60'],
            'name.lv' => ['nullable', 'string', 'max:60'],
            'name.ru' => ['nullable', 'string', 'max:60'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => (string) __('admin-floors-name-required'),
            'name.en.required' => (string) __('admin-floors-name-required'),
        ];
    }

    /**
     * The name in all three languages. An empty Latvian or Russian name takes the English one.
     *
     * @return array{en: string, lv: string, ru: string}
     */
    public function names(): array
    {
        $english = $this->string('name.en')->trim()->value();
        $latvian = $this->string('name.lv')->trim()->value();
        $russian = $this->string('name.ru')->trim()->value();

        return [
            'en' => $english,
            'lv' => $latvian === '' ? $english : $latvian,
            'ru' => $russian === '' ? $english : $russian,
        ];
    }
}
