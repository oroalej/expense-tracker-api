<?php

namespace App\Rules;

use App\Models\Model;
use Illuminate\Contracts\Validation\Rule;
use Throwable;

class IsEntryExist implements Rule
{
	/**
	 * Create a new rule instance.
	 *
	 * @return void
	 * @throws Throwable
	 */
	public function __construct(public string $namespace)
	{
	}

	/**
	 * Determine if the validation rule passes.
	 *
	 * @param string $attribute
	 * @param mixed  $value
	 * @return bool
	 */
	public function passes($attribute, $value): bool
	{
		if (!class_exists($this->namespace) || $value === null) {
			return false;
		}

		$model = app($this->namespace);

		if (!$model instanceof Model) {
			return false;
		}

		return $model::where('id', $value)->exists();
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
