<?php

namespace App\Http\Requests\Store;

use App\Http\Requests\CustomRequest;
use App\Models\Category;
use App\Models\Ledger;
use Illuminate\Support\Facades\Gate;

/**
 * @property int $category_group_id
 * @property string $name
 * @property string $notes
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
            'name'  => 'required|max:255',
            'notes' => 'nullable|max:255',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'  => 'Name',
            'notes' => 'Notes',
        ];
    }
}
