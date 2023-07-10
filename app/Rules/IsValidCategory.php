<?php

namespace App\Rules;

use App\Models\Category;
use App\Models\Ledger;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class IsValidCategory implements Rule
{
    public function __construct(protected Ledger $ledger)
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
        return Category::where(function (Builder $builder) use ($value) {
            $builder->where('ledger_id', $this->ledger->id)
                ->where('id', $value);
        })
            ->orWhere(function (Builder $builder) use ($value) {
                $builder->whereNull('ledger_id')
                    ->where('id', $value);
            })
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
