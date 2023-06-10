<?php

namespace App\Http\Requests\Update;

use App\Http\Requests\CustomRequest;
use App\Models\Category;
use App\Models\Ledger;
use Illuminate\Support\Facades\Gate;

/**
 * @property int $category_group_id
 * @property string $name
 * @property string $notes
 * @property-read Category $category
 * @property-read Ledger $ledger
 */
class UpdateCategoryRequest extends CustomRequest
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
            'name'  => 'required|max:255',
            'notes' => 'string|nullable|max:255',
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
