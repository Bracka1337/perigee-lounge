<?php

namespace App\Http\Requests;

use App\Services\Render\RenderStudio;
use Illuminate\Foundation\Http\FormRequest;

class RenderStudioRequest extends FormRequest
{
    /**
     * The studio answers only where it is enabled (404 otherwise), and only to an admin
     * or to a signed link made for the headless browser.
     */
    public function authorize(): bool
    {
        abort_unless(app(RenderStudio::class)->enabled(), 404);

        return $this->hasValidSignature() || $this->user()?->can('admin') === true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'scene' => ['required', 'in:room,table,menu'],
            'id' => ['required', 'integer', 'min:1'],
        ];
    }
}
