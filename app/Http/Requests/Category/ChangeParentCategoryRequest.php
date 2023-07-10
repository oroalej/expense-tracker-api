<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\CustomRequest;
use App\Models\Category;
use App\Rules\IsSameCategoryType;
use App\Rules\IsTopLevelCategory;
use Illuminate\Support\Facades\Gate;

/**
 * @property-read Category $category
 */
class ChangeParentCategoryRequest extends CustomRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                new IsSameCategoryType($this->category->category_type->value),
                new IsTopLevelCategory()
            ]
        ];
    }
}
