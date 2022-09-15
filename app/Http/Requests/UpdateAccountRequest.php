<?php

namespace App\Http\Requests;

use App\Models\Ledger;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $name
 * @property float $current_balance
 * @property-read Ledger $ledger
 */
class UpdateAccountRequest extends FormRequest
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
            'name'            => 'required|max:255',
            'current_balance' => 'required|integer',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'            => 'Name',
            'current_balance' => 'Current Balance'
        ];
    }
}
