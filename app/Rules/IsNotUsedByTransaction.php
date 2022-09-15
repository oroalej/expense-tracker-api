<?php

namespace App\Rules;

use App\Models\Model;
use Illuminate\Contracts\Validation\Rule;

class IsNotUsedByTransaction implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(protected Model $model)
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
        if (! method_exists($this->model, 'transactions')) {
            return false;
        }

        return ! $this->model->transactions()->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Is currently being used by a transaction';
    }
}
