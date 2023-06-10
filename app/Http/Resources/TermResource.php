<?php

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read  int $id
 * @property-read  string $name
 * @property-read  Collection $accountTypes
 */
class TermResource extends JsonResource
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
            'id'            => $this->id,
            'name'          => $this->name,
            'account_types' => TermResource::collection($this->whenLoaded('accountTypes'))
        ];
    }
}
