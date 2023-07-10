<?php

namespace App\Http\Requests\Category;

use App\Enums\CategoryTypeState;
use App\Http\Requests\CustomRequest;
use App\Models\Category;
use App\Models\Ledger;
use App\Rules\IsOwnData;
use App\Rules\IsSameCategoryType;
use App\Rules\IsTopLevelCategory;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

/**
 * @property string $name
 * @property string $notes
 * @property integer $category_type
 * @property Ledger $ledger
 */
class StoreCategoryRequest extends CustomRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Gate::allows('store', Category::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'          => 'required|max:255',
            'category_type' => [
                'required',
                new Enum(CategoryTypeState::class)
            ],
            'parent_id'     => [
                'nullable',
                new IsTopLevelCategory(),
                new IsOwnData($this->ledger, Category::class),
                new IsSameCategoryType($this->category_type)
            ],
            'notes'         => 'nullable|max:255',
            'is_visible'    => 'nullable|boolean',
            'is_budgetable' => 'nullable|boolean',
            'is_reportable' => 'nullable|boolean',
            'order'         => 'nullable|integer'
        ];
    }

    public function attributes(): array
    {
        return [
            'name'          => 'Name',
            'notes'         => 'Notes',
            'order'         => 'Order',
            'category_type' => 'Type',
            'parent_id'     => 'Parent',
            'is_visible'    => 'Visible',
            'is_reportable' => 'Report',
            'is_budgetable' => 'Budget',
        ];
    }
}
