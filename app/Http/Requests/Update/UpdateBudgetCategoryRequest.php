<?php

namespace App\Http\Requests\Update;

use App\Http\Requests\CustomRequest;

class UpdateBudgetCategoryRequest extends CustomRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|gte:0',
        ];
    }
}
