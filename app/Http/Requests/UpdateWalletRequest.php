<?php

namespace App\Http\Requests;

use App\Enums\WalletAccessTypeState;
use App\Enums\WalletTypeState;
use App\Models\Wallet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

/**
 * @property string $name
 * @property string $description
 * @property int $current_balance
 * @property int $wallet_type
 * @property array $access
 * @property-read Wallet $wallet
 */
class UpdateWalletRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Gate::allows('update', $this->wallet);
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'                 => 'required|max:191',
            'description'          => 'nullable|max:191',
            'current_balance'      => 'integer',
            'wallet_type'          => [
                'required',
                new Enum(WalletTypeState::class)
            ],
            'access'               => 'nullable|array',
            'access.*.access_type' => [
                'required',
                new Enum(WalletAccessTypeState::class)
            ],
            'access.*.email'       => 'required|email|exists:users,email',
            'access.*.start_date'  => 'required|date|date_format:Y-m-d',
            'access.*.end_date'    => 'nullable|date|after_or_equal:access.*.start_date|date_format:Y-m-d',
        ];
    }
    
    public function attributes(): array
    {
        return [
            'name'            => 'Name',
            'description'     => 'Description',
            'current_balance' => 'Current Balance',
            'wallet_type'     => 'Type'
        ];
    }
}
