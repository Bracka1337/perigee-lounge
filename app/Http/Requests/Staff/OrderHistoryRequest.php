<?php

namespace App\Http\Requests\Staff;

use App\Enums\OrderStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

/**
 * Filters for the closed-order history on the staff orders page.
 */
class OrderHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string|ValidationRule|Enum>>
     */
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'status' => ['nullable', Rule::enum(OrderStatus::class)->only(OrderStatus::closed())],
        ];
    }

    /**
     * Send bad filters back to the unfiltered page, not to the previous URL, which may be this same request.
     */
    protected function getRedirectUrl(): string
    {
        // A null date is left out of the query string.
        return route('staff.orders.index', [
            'locale' => $this->route('locale'),
            'date' => is_string($this->query('date')) ? $this->query('date') : null,
        ]);
    }

    public function historyFrom(): ?Carbon
    {
        $from = $this->validated('from');

        return is_string($from) ? Carbon::parse($from)->startOfDay() : null;
    }

    public function historyTo(): ?Carbon
    {
        $to = $this->validated('to');

        return is_string($to) ? Carbon::parse($to)->startOfDay() : null;
    }

    public function historyStatus(): ?OrderStatus
    {
        $status = $this->validated('status');

        return is_string($status) ? OrderStatus::from($status) : null;
    }
}
