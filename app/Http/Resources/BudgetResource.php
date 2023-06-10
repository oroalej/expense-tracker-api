<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Str;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $notes
 * @property-read int $month
 * @property-read int $year
 * @property-read Collection $budgetCategories
 * @property-read Carbon $date
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Carbon|null $deleted_at
 */
class BudgetResource extends JsonResource
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
            'month'      => $this->month,
            'year'       => $this->year,
            'year_month' => $this->year.Str::padLeft($this->month, 2, 0),
            'date'       => $this->date,
            'created_at' => $this->whenColumnLoaded('created_at'),
            'updated_at' => $this->whenColumnLoaded('updated_at'),
            'deleted_at' => $this->whenColumnLoaded('deleted_at'),
        ];
    }
}
