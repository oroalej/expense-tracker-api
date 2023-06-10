<?php

namespace App\Http\Resources;

use App\Models\Currency;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property-read string $name
 * @property-read int $id
 * @property-read string $user_id
 * @property-read string $date_format
 * @property-read bool is_archived
 * @property-read int $currency_placement
 * @property-read Currency $currency
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Carbon|null $archived_at
 * @property-read Carbon|null $deleted_at
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
        return [
            'id'            => Hashids::encode($this->id),
            'number_format' => new CurrencyResource($this->whenLoaded('currency')),
            'name'          => $this->name,
            'date_format'   => $this->date_format,
            'is_archived'   => $this->is_archived,
            'created_at'    => $this->created_at,
            'archived_at'   => $this->archived_at,
            'updated_at'    => $this->updated_at,
            'deleted_at'    => $this->deleted_at,
        ];
    }
}
