<?php

namespace App\Http\Requests;

use App\Models\Ledger;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property int $ledger_id
 * @property string $name
 * @property string $notes
 * @property-read Ledger $ledger
 */
class UpdateCategoryGroupRequest extends FormRequest
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
