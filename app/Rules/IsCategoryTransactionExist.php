<?php

namespace App\Rules;

use App\Models\Category;
use Illuminate\Contracts\Validation\Rule;

class IsCategoryTransactionExist implements Rule
{
    public function __construct(protected Category $category)
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
        if ($this->category->category_type !== $value && $this->category->transactions()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Category Type cannot be changed when already used.';
    }
}
