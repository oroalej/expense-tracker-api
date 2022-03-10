<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Rules\IsEntryExist;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

/**
 * @property int $category_id
 * @property int $wallet_id
 * @property int $tag_id
 * @property string $amount
 * @property string $remarks
 * @property array $tags
 * @property Carbon $transaction_date
 * @property-read Transaction $transaction
 */
class UpdateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Gate::allows('update', $this->transaction);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|integer',
            'remarks' => 'required|max:191',
            'transaction_date' => 'required|date|date_format:Y-m-d',
            'category_id' => ['required', new IsEntryExist(Category::class)],
            'wallet_id' => ['required', new IsEntryExist(Wallet::class)],
            'tags' => ['nullable|array'],
        ];
    }

    public function attributes(): array
    {
        return [
            'amount' => 'Amount',
            'remarks' => 'Remarks',
            'transaction_date' => 'Transaction Date',
            'category_id' => 'Category',
            'wallet_id' => 'Wallet',
        ];
    }
}
