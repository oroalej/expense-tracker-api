<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property-read int $id
 * @property-read string budget_id
 * @property-read string $category_id
 * @property-read int $available
 * @property-read int $assigned
 * @property-read int $activity
 * @property-read Collection $transactions
 */
class BudgetCategoryResource extends JsonResource
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
            'id'         => Hashids::encode($this->id),
            'available'  => $this->available,
            'assigned'   => $this->assigned,
            'activity'   => $this->activity,
            'created_at' => $this->whenColumnLoaded('created_at'),
            'updated_at' => $this->whenColumnLoaded('updated_at'),
            $this->mergeWhen($this->budget_id, [
                'budget_id' => Hashids::encode($this->budget_id),
            ]),
            $this->mergeWhen($this->category_id, [
                'category_id' => Hashids::encode($this->category_id),
            ]),
            $this->mergeWhen($this->transactions instanceof Collection, [
                'transactions' => TransactionResource::collection($this->transactions ?? []),
            ])
        ];
    }
}
