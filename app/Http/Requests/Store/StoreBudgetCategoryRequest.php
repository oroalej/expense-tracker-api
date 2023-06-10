<?php

namespace App\Http\Requests\Store;

use App\Http\Requests\CustomRequest;
use App\Models\Category;
use App\Rules\IsOwnData;

class StoreBudgetCategoryRequest extends CustomRequest
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
            'amount'      => 'required|numeric',
            'category_id' => [
                'required',
                new IsOwnData($this->ledger, Category::class)
            ]
        ];
    }
}
