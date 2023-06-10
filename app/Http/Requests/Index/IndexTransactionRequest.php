<?php

namespace App\Http\Requests\Index;

use App\Http\Requests\CustomRequest;
use App\Rules\IsValidHashId;

class IndexTransactionRequest extends CustomRequest
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
            'category_id' => [
                'nullable',
                new IsValidHashId()
            ],
            'account_id' => [
                'nullable',
                new IsValidHashId()
            ]
        ];
    }
}
