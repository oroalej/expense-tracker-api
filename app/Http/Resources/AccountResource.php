<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read int $account_type_id
 * @property-read int $ledger_id
 * @property-read float $current_balance
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Carbon|null $deleted_at
 */
class AccountResource extends JsonResource
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
            'id'              => Hashids::encode($this->id),
            'name'            => $this->name,
            'current_balance' => $this->current_balance,
            'created_at'      => $this->whenColumnLoaded('created_at'),
            'updated_at'      => $this->whenColumnLoaded('updated_at'),
            'deleted_at'      => $this->whenColumnLoaded('deleted_at'),
            $this->mergeWhen($this->ledger_id, [
                'ledger_id' => Hashids::encode($this->ledger_id)
            ]),
            $this->mergeWhen($this->account_type_id, [
                'account_type_id' => Hashids::encode($this->account_type_id)
            ]),
        ];
    }
}
