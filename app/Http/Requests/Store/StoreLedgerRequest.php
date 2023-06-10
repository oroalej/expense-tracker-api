<?php

namespace App\Http\Requests\Store;

use App\Enums\DateFormatState;
use App\Http\Requests\CustomRequest;
use App\Rules\IsValidHashId;
use Illuminate\Validation\Rules\Enum;

/**
 * @property string $name
 * @property string $date_format_id
 * @property string $currency_placement_id
 * @property string $number_format_id
 * @property string $currency_id
 */
class StoreLedgerRequest extends CustomRequest
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
