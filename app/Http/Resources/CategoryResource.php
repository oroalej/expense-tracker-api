<?php

namespace App\Http\Resources;

use App\Models\CategoryGroup;
use App\Models\Ledger;
use Carbon\Carbon;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $notes
 * @property-read int $order
 * @property-read int category_group_id
 * @property-read int ledger_id
 * @property-read boolean is_hidden
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Carbon|null $deleted_at
 * @property-read CategoryGroup $categoryGroup
 * @property-read Ledger $ledger
 */
class CategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                 => Hashids::encode($this->id),
            'category_group_id'  => Hashids::encode($this->category_group_id),
            'ledger_id'          => Hashids::encode($this->ledger_id),
            'name'               => $this->name,
            'notes'              => $this->notes ?? '',
            'order'              => $this->order,
            'is_hidden'          => $this->is_hidden,
            'created_at'         => $this->whenColumnLoaded('created_at'),
            'updated_at'         => $this->whenColumnLoaded('updated_at'),
            'deleted_at'         => $this->whenColumnLoaded('deleted_at'),
            'transactions_count' => $this->whenCounted('transactions'),
        ];
    }
}
