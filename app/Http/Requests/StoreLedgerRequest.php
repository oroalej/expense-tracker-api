<?php

namespace App\Http\Requests;

use App\Enums\TaxonomyState;
use App\Models\Currency;
use App\Rules\IsDataExist;
use App\Rules\IsTermExist;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $name
 * @property string $date_format_id
 * @property string $currency_placement_id
 * @property string $number_format_id
 * @property string $currency_id
 */
class StoreLedgerRequest extends FormRequest
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
            'name' => 'required|max:255',
            //            'currency_id'           => [
            //                'required',
            //                new IsDataExist(Currency::class)
            //            ],
            //            'date_format_id'        => [
            //                'required',
            //                new IsTermExist(TaxonomyState::DateFormats)
            //            ],
            //            'currency_placement_id' => [
            //                'required',
            //                new IsTermExist(TaxonomyState::CurrencyPlacements)
            //            ],
            //            'number_format_id'      => [
            //                'required',
            //                new IsTermExist(TaxonomyState::NumberFormats)
            //            ]
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Name',
            //            'currency_id'           => 'Currency',
            //            'date_format_id'        => 'Date Format',
            //            'currency_placement_id' => 'Currency Placement',
            //            'number_format_id'      => 'Number Format'
        ];
    }
}
