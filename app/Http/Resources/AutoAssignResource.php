<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property-read int $id
 * @property-read int $average_assigned
 * @property-read int $average_spent
 * @property-read int $assigned_last_month
 * @property-read int $spent_last_month
 */
class AutoAssignResource extends JsonResource
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
            'id'                  => Hashids::encode($this->id),
            'name' => $this->name,
            'average_assigned'    => $this->average_assigned,
            'average_spent'       => $this->average_spent,
            'assigned_last_month' => $this->assigned_last_month,
            'spent_last_month'    => $this->spent_last_month
        ];
    }
}
