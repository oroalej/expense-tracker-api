<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class OnlyOneOfInflowOrOutflowIsField implements Rule
{
    public function __construct(public $amount)
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        // NULL & NULL, NULL & 0, 0 & NULL, 0 & 0
        if (empty($value) && empty($this->amount)) {
            return false;
        }

        // 1000 & 1000
        return !(!empty($value) && !empty($this->amount));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return "Only one of inflow or outflow fields can be filled.";
    }
}
