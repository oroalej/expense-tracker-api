<?php

namespace App\Http\Requests\Store;

use App\Http\Requests\CustomRequest;
use App\Models\CategoryGroup;
use App\Models\Ledger;
use Gate;

/**
 * @property int $ledger_id
 * @property string $name
 * @property string $notes
 * @property-read Ledger $ledger
 */
class StoreCategoryGroupRequest extends CustomRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Gate::allows('store', CategoryGroup::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:255',
            'notes' => 'nullable|max:255',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'notes' => 'Notes',
        ];
    }
}
