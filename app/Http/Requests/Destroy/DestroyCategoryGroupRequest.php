<?php

namespace App\Http\Requests\Destroy;

use App\Http\Requests\CustomRequest;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Ledger;
use App\Rules\IsOwnData;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property-read CategoryGroup $categoryGroup
 * @property-read Ledger $ledger
 */
class DestroyCategoryGroupRequest extends CustomRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Gate::allows('delete', $this->categoryGroup);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $transactionExists = $this->categoryGroup
            ->categories()
            ->has('transactions')
            ->exists();

        if ($transactionExists) {
            $categoriesHashId = $this->categoryGroup
                ->categories()
                ->pluck('id')
                ->map(fn ($categoryId) => Hashids::encode($categoryId))
                ->toArray();

            return [
                'category_id' => [
                    'required',
                    Rule::notIn($categoriesHashId),
                    new IsOwnData($this->ledger, Category::class)
                ],
            ];
        }

        return [];
    }
}
