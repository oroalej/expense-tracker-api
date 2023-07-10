<?php

namespace App\Http\Requests\Index;

use App\Http\Requests\CustomRequest;
use App\Models\Account;
use App\Rules\IsOwnData;
use Illuminate\Validation\Rule;

class IndexTransactionRequest extends CustomRequest
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
            'account_id' => [
                'nullable',
                new IsOwnData($this->ledger, Account::class)
            ],
            'categories' => [
                'nullable',
                'regex:/^(included|excluded)\:(([\w]{12})[\,]*)+$/'
            ],
            'amount'     => [
                'nullable',
                'regex:/^(NOT_EMPTY|EMPTY)|(BETWEEN)\,([\d]+)\,([\d]+)|(EQUAL|GT|GTE|LT|LTE)\,([\d]+)$/'
            ],
            'sort'       => [
                'nullable',
                'regex:/^((transaction_date|amount|category_id)\:(asc|desc)[\,]*)*$/'
            ],
            'state'      => [
                Rule::in(['action', 'clear'])
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'categories.regex' => __('validation.exists'),
            'amount.regex'     => __('validation.exists'),
            'sort.regex'       => __('validation.exists')
        ];
    }
}
