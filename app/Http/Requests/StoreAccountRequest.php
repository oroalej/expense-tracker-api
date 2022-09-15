<?php

namespace App\Http\Requests;

use App\Models\Ledger;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $name
 * @property int $account_type_id
 * @property float $current_balance
 * @property-read Ledger $ledger
 */
class StoreAccountRequest extends FormRequest
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
            'account_type_id' => 'required|exists:App\Models\AccountType,uuid',
        ];
    }
}
