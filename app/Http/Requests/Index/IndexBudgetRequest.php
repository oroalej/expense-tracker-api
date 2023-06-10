<?php

namespace App\Http\Requests\Index;

use App\Models\Ledger;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read Ledger $ledger
 */
class IndexBudgetRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'year' => 'nullable|integer|regex:/^\d{4}$/',
        ];
    }
}
