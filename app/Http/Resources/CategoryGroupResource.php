<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $uuid
 * @property string $name
 * @property string $notes
 * @property bool $is_hidden
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class CategoryGroupResource extends JsonResource
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
            'uuid'      => $this->uuid,
            'name'      => $this->name,
            'notes'     => $this->notes,
            'is_hidden' => $this->is_hidden,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            //            'ledger' => new LedgerResource($this->whenLoaded('ledger')),
            //            'categories' => CategoryResource::collection(
            //                $this->whenLoaded('categories')
            //            ),
        ];
    }
}
