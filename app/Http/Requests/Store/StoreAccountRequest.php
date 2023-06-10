<?php

namespace App\Http\Requests\Store;

use App\Http\Requests\CustomRequest;
use App\Models\Account;
use App\Models\Ledger;
use App\Rules\IsValidHashId;
use Illuminate\Support\Facades\Gate;

/**
 * @property string $name
 * @property int $account_type_id
 * @property float $current_balance
 * @property-read Ledger $ledger
 */
class StoreAccountRequest extends CustomRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Gate::allows('store', Account::class);
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
            'account_type_id' => [
                'required',
                new IsValidHashId()
            ]
        ];
    }
}
