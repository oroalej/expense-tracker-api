<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * @property-read string $uuid
 * @property-read string $name
 * @property-read string $email
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 */
class UserResource extends JsonResource
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
            'id'         => $this->uuid,
            'name'       => $this->name,
            'email'      => $this->email,
            'created_at' => $this->whenColumnLoaded('created_at'),
            'updated_at' => $this->whenColumnLoaded('updated_at'),
            'ledgers'    => LedgerResource::collection($this->whenLoaded('ledgers')),
        ];
    }
}
