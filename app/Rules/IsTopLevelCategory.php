<?php

namespace App\Rules;

use App\Models\Category;
use Illuminate\Contracts\Validation\Rule;

class IsTopLevelCategory implements Rule
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
        return Category::where('id', $value)
            ->whereNull('parent_id')
            ->exists();
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
