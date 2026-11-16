<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Creating and editing a menu item. Photos: jpg, png or webp up to 4 MB. The pixel cap
 * keeps GD's memory use bounded while it resizes.
 */
class MenuItemRequest extends FormRequest
{
    public const MAX_PHOTO_KB = 4096;

    public const MAX_PHOTO_SIDE = 5000;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        $locales = config('site.locales', ['ru']);
        $default = config('site.default_locale', $locales[0] ?? 'ru');

        return [
            'menu_category_id' => ['required', 'integer', 'exists:menu_categories,id'],

            'name' => ['required', 'array'],
            "name.$default" => ['required', 'string', 'max:255'],
            'name.*' => ['nullable', 'string', 'max:255'],

            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string'],

            'allergens' => ['nullable', 'array'],
            'allergens.*' => ['nullable', 'string'],

            'price' => ['required', 'numeric', 'min:0'],
            'image' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:'.self::MAX_PHOTO_KB,
                'dimensions:max_width='.self::MAX_PHOTO_SIDE.',max_height='.self::MAX_PHOTO_SIDE,
            ],
            'remove_image' => ['nullable', 'boolean'],
            'is_available' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $rules = (string) __('staff-menu-photo-rules');

        return [
            'image.file' => $rules,
            'image.mimes' => $rules,
            'image.max' => $rules,
            'image.dimensions' => $rules,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function itemData(): array
    {
        return [
            'menu_category_id' => (int) $this->validated('menu_category_id'),
            'name' => $this->validated('name'),
            'description' => $this->validated('description') ?? [],
            'allergens' => $this->validated('allergens') ?? [],
            'price' => $this->validated('price'),
        ];
    }
}
