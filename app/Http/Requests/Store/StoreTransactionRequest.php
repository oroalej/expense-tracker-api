<?php

namespace App\Http\Requests\Store;

use App\Http\Requests\CustomRequest;
use App\Models\Account;
use App\Models\Category;
use App\Models\Ledger;
use App\Models\Transaction;
use App\Rules\IsOwnData;
use App\Rules\OnlyOneOfInflowOrOutflowIsField;
use Carbon\Carbon;
use Gate;

/**
 * @property int $category_id
 * @property string $account_id
 * @property int $inflow
 * @property int $outflow
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
            'inflow'           => [
                'numeric',
                'regex:/^(?:[0-9]){1,9}(?:\.\d)?\d?$/',
                new OnlyOneOfInflowOrOutflowIsField($this->outflow),
            ],
            'outflow'          => [
                'numeric',
                'regex:/^(?:[0-9]){1,9}(?:\.\d)?\d?$/',
                new OnlyOneOfInflowOrOutflowIsField($this->inflow),
            ],
            'remarks'          => 'nullable|max:255',
            'transaction_date' => 'required|date|date_format:Y-m-d',
            'is_approved'      => 'nullable|boolean',
            'is_cleared'       => 'nullable|boolean',
            'category_id'      => [
                'required',
                new IsOwnData($this->ledger, Category::class)
            ],
            'account_id'       => [
                'required',
                new IsOwnData($this->ledger, Account::class)
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
            'inflow.regex'  => __('validation.numeric'),
            'outflow.regex' => __('validation.numeric'),
        ];
    }
}
