<?php

namespace App\Http\Resources;

use App\Enums\CurrentPlacementState;
use App\Models\Currency;
use App\Models\Term;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $name
 * @property string $uuid
 * @property string $user_id
 * @property Term $dateFormat
 * @property int $currencyPlacement
 * @property Term $numberFormat
 * @property Currency $currency
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class LedgerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
//        $numberFormat = explode('', $this->numberFormat->name);

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            //            'currency_format' => [
            //                'code'                => $this->currency->code,
            //                "example_format"      => number_format(123456.78, $numberFormat[0], $numberFormat[1], $numberFormat[2]),
            //                'thousands_separator' => $numberFormat[2],
            //                'decimal_separator'   => $numberFormat[1],
            //                'decimal_digits'      => $numberFormat[0],
            //                'currency_symbol'     => $this->currency->abbr,
            //                'symbol_first'        => $this->currencyPlacement === CurrentPlacementState::Beginning->value,
            //                'symbol_visible'      => $this->currencyPlacement !== CurrentPlacementState::Hidden->value
            //            ],
            //            'date_format'     => $this->dateFormat->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
