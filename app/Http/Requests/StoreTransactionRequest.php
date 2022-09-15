<?php

namespace App\Http\Requests;

use App\Models\Account;
use App\Models\Category;
use App\Rules\IsDataExist;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property int $category_id
 * @property string $account_id
 * @property int $inflow
 * @property int $outflow
 * @property string $remarks
 * @property Carbon $transaction_date
 */
class StoreTransactionRequest extends FormRequest
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
            'inflow'           => ['nullable', 'regex:/^(?:[0-9]){1,9}(?:\.\d)?\d?$/'],
            'outflow'          => [
                'nullable',
                'required_if:inflow,null',
                'regex:/^(?:[0-9]){1,9}(?:\.\d)?\d?$/',
            ],
            'remarks'          => 'nullable|max:255',
            'transaction_date' => 'required|date|date_format:Y-m-d',
            'category_id'      => ['required', new IsDataExist(Category::class)],
            'account_id'       => ['required', new IsDataExist(Account::class)],
            'is_approved'      => 'nullable|boolean',
            'is_cleared'       => 'nullable|boolean',
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
