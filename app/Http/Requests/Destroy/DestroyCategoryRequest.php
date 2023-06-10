<?php

namespace App\Http\Requests\Destroy;

use App\Http\Requests\CustomRequest;
use App\Models\Category;
use App\Rules\IsOwnData;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property-read Category $category
 * @property-read string $category_id
 */
class DestroyCategoryRequest extends CustomRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Gate::allows('delete', $this->category);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $transactionExist = $this->category
            ->transactions()
            ->exists();

        if ($transactionExist) {
            return [
                'category_id' => [
                    'required',
                    Rule::notIn([Hashids::encode($this->category->id)]),
                    new IsOwnData($this->ledger, Category::class)
                ],
            ];
        }

        return [];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'Category'
        ];
    }
}
