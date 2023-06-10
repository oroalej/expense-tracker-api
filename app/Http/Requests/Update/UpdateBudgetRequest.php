<?php

namespace App\Http\Requests\Update;

use App\Http\Requests\CustomRequest;

class UpdateBudgetRequest extends CustomRequest
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
            'notes' => 'nullable|max:255|string',
        ];
    }
}
