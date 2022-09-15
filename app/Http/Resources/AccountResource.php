<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property float $current_balance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
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
            'uuid'            => $this->uuid,
            'name'            => $this->name,
            'current_balance' => $this->current_balance,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
            'deleted_at'      => $this->deleted_at,
        ];
    }
}
