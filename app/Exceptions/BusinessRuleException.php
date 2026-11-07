<?php

namespace App\Exceptions;

use Illuminate\Http\RedirectResponse;
use RuntimeException;

/**
 * Thrown by actions when a business rule blocks the operation, such as a table
 * that already has an open order. Carries a translation key for the message.
 * Uncaught, it sends the user back with the message as a flash error.
 */
class BusinessRuleException extends RuntimeException
{
    public function __construct(public readonly string $translationKey)
    {
        parent::__construct($translationKey);
    }

    public function render(): RedirectResponse
    {
        return back()->withInput()->with('error', __($this->translationKey));
    }
}
