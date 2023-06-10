<?php

namespace App\Http\Requests\Update;

use App\Http\Requests\CustomRequest;
use App\Models\Account;
use App\Models\Ledger;
use Gate;

/**
 * @property-read string $name
 * @property-read float $current_balance
 * @property-read Ledger $ledger
 * @property-read Account $account
 */
class UpdateAccountRequest extends CustomRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Gate::allows('update', $this->account);
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
            'current_balance' => 'Current Balance',
        ];
    }
}
