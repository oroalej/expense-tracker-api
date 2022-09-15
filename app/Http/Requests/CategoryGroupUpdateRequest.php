<?php

namespace App\Http\Requests;

use App\Models\CategoryGroup;
use App\Models\Ledger;
use App\Rules\IsOwnData;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read $id
 * @property-read Ledger $ledger
 */
class CategoryGroupUpdateRequest extends FormRequest
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
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => [
                new IsOwnData($this->ledger, CategoryGroup::class)
            ]
        ];
    }
}
