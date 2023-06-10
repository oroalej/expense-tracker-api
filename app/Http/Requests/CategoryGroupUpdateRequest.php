<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Rules\IsOwnData;
use Gate;

/**
 * @property-read $id
 * @property-read Category $category
 */
class CategoryGroupUpdateRequest extends CustomRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Gate::allows('update', $this->category);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'category_group_id' => [
                'required',
                new IsOwnData($this->ledger, CategoryGroup::class),
            ],
        ];
    }
}
