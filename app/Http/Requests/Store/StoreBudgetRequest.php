<?php

namespace App\Http\Requests\Store;

use App\Http\Requests\CustomRequest;

class StoreBudgetRequest extends CustomRequest
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
            'year' => 'required|integer|regex:/^\d{4}$/',
            'month' => 'required|integer|between:1,12',
        ];
    }
}
