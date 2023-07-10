<?php

namespace App\Rules;

use App\Models\Category;
use Illuminate\Contracts\Validation\Rule;

class IsSameCategoryType implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(protected int|null $categoryType)
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  int|null  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if ($this->categoryType === null || $value === null) {
            return false;
        }

        $categoryType = Category::find($value)?->getAttribute('category_type');

        return $categoryType?->value === $this->categoryType;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return __('validation.same');
    }
}
