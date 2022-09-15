<?php

namespace App\Rules;

use App\Enums\TaxonomyState;
use App\Models\Term;
use Illuminate\Contracts\Validation\Rule;

class IsTermExist implements Rule
{
    public function __construct(public TaxonomyState $taxonomyState)
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
        return Term::where('taxonomy_id', $this->taxonomyState->value)
            ->where('uuid', $value)
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
