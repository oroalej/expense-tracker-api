<?php

namespace App\Http\Requests\Store;

use App\Enums\DefaultCategoryIDs;
use App\Http\Requests\CustomRequest;
use App\Models\Account;
use App\Models\Ledger;
use App\Models\Transaction;
use App\Rules\IsOwnData;
use App\Rules\IsValidCategory;
use Carbon\Carbon;
use Gate;
use Illuminate\Validation\Rule;

/**
 * @property int $category_id
 * @property int $account_id
 * @property int $amount
 * @property string $remarks
 * @property Carbon $transaction_date
 * @property Ledger $ledger
 */
class StoreTransactionRequest extends CustomRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Gate::allows('store', Transaction::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'remarks'          => 'nullable|max:255',
            'transaction_date' => 'required|date|date_format:Y-m-d',
            'is_approved'      => 'nullable|boolean',
            'is_cleared'       => 'nullable|boolean',
            'category_id'      => [
                'required',
                new IsValidCategory($this->ledger)
            ],
            'account_id'       => [
                'required',
                new IsOwnData($this->ledger, Account::class)
            ],
            'transfer_id'      => [
                'nullable',
                Rule::requiredIf($this->category_id === DefaultCategoryIDs::TRANSFER->value),
                new IsOwnData($this->ledger, Account::class)
            ],
            'amount'           => [
                'required',
                'integer',
                'between:1,9999999999999',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'remarks'          => 'Remarks',
            'transaction_date' => 'Transaction Date',
            'category_id'      => 'Category',
            'account_id'       => 'Account',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.between' => 'The :attribute must not exceed :digits digits.'
        ];
    }
}
