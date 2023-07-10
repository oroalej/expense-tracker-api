<?php

namespace App\Rules;

use App\Models\Ledger;
use App\Models\Model;
use Illuminate\Contracts\Validation\Rule;

class IsOwnData implements Rule
{
    /**
     * @param  Ledger  $ledger
     * @param  class-string  $namespace
     */
    public function __construct(public Ledger $ledger, public string $namespace)
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
        if (! class_exists($this->namespace) || $value === null) {
            return false;
        }

        /** @var Model $model */
        $model = app($this->namespace);

        if (! $model instanceof Model) {
            return false;
        }

        return $model::where('ledger_id', $this->ledger->id)
            ->where($model->getRouteKeyName(), $value)
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
