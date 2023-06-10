<?php

namespace App\Http\Resources;

use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $notes
 * @property-read int $order
 * @property-read bool $is_hidden
 * @property Collection $categories
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Carbon|null $deleted_at
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
        $categoryIds = [];

        if ($this->categories instanceof Collection) {
            $categoryIds = $this->categories->map(fn (Category $category) => Hashids::encode($category->id));
        }

        return [
            'id'         => Hashids::encode($this->id),
            'name'       => $this->name,
            'notes'      => $this->notes ?? '',
            'order'      => $this->order,
            'is_hidden'  => $this->is_hidden,
            'created_at' => $this->whenColumnLoaded('created_at'),
            'updated_at' => $this->whenColumnLoaded('updated_at'),
            'deleted_at' => $this->whenColumnLoaded('deleted_at'),
            'categories' => $categoryIds
        ];
    }
}
