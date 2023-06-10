<?php

namespace App\Http\Requests\Update;

use App\Enums\DateFormatState;
use App\Http\Requests\CustomRequest;
use App\Rules\IsValidHashId;
use Gate;
use Illuminate\Validation\Rules\Enum;

/**
 * @property string $name
 */
class UpdateLedgerRequest extends CustomRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Gate::allows('update', $this->ledger);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'        => 'required|max:255',
            'currency_id' => [
                'required',
                new IsValidHashId()
            ],
            'date_format' => [
                'required', 'string',
                new Enum(DateFormatState::class)
            ]
        ];
    }

    public function attributes(): array
    {
        return [
            'name'        => 'Name',
            'date_format' => 'Date Format',
            'currency_id' => 'Currency',
        ];
    }
}
