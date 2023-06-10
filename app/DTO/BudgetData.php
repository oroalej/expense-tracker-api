<?php

namespace App\DTO;

use App\Http\Requests\Store\StoreBudgetRequest;
use App\Models\Ledger;

class BudgetData
{
    public function __construct(
        public readonly int $month,
        public readonly int $year,
        public readonly ?Ledger $ledger = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'month' => $this->month,
            'year'  => $this->year,
        ];
    }

    /**
     * @param  StoreBudgetRequest  $request
     * @return BudgetData
     */
    public static function fromRequest(StoreBudgetRequest $request): BudgetData
    {
        return new self(
            month: $request->validated('month'),
            year: $request->validated('year'),
            ledger: $request->ledger,
        );
    }
}
