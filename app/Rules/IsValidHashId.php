<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Vinkla\Hashids\Facades\Hashids;

class IsValidHashId implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $decoded = Hashids::decode($value);

        return (bool) count($decoded);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return __('validation.exists');
    }
}
