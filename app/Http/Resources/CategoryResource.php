<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Models\Ledger;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $notes
 * @property-read int $order
 * @property-read int ledger_id
 * @property-read int parent_id
 * @property-read int category_type
 * @property-read int transactions_count
 * @property-read boolean is_visible
 * @property-read boolean is_budgetable
 * @property-read boolean is_reportable
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Carbon|null $deleted_at
 * @property-read Collection $child
 * @property-read Ledger $ledger
 */
class CategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        $categoryIds = [];

        if ($this->child instanceof Collection) {
            $categoryIds = $this->child
                ->map(fn (Category $category) => Hashids::encode($category->id))
                ->toArray();
        }

        return [
            'id'                 => Hashids::encode($this->id),
            'parent_id'          => Hashids::encode($this->parent_id),
            'category_type'      => $this->category_type,
            'name'               => $this->name,
            'notes'              => $this->notes,
            'order'              => $this->order,
            'is_visible'         => $this->is_visible,
            'is_budgetable'      => $this->is_budgetable,
            'is_reportable'      => $this->is_reportable,
            'created_at'         => $this->whenColumnLoaded('created_at'),
            'updated_at'         => $this->whenColumnLoaded('updated_at'),
            'deleted_at'         => $this->whenColumnLoaded('deleted_at'),
            'transactions_count' => $this->whenCounted('transactions', $this->transactions_count, 0),
            'child'              => $categoryIds
        ];
    }
}
