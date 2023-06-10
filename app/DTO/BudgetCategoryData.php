<?php

namespace App\DTO;

use App\Models\Budget;
use App\Models\Category;

class BudgetCategoryData
{
    public function __construct(
        public readonly Category $category,
        public readonly Budget $budget,
        public readonly ?int $assigned = 0,
        public readonly ?int $available = 0,
        public readonly ?int $activity = 0,
    ) {
    }

    public function toArray(): array
    {
        return [
            'assigned'  => $this->assigned,
            'available' => $this->available,
            'activity'  => $this->activity,
        ];
    }
}
