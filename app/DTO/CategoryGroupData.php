<?php

namespace App\DTO;

use App\Http\Requests\Store\StoreCategoryGroupRequest;
use App\Models\Ledger;

class CategoryGroupData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $notes,
        public readonly Ledger $ledger,
        public readonly ?int $order = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'name'  => $this->name,
            'notes' => $this->notes,
            'order' => $this->order,
        ];
    }

    /**
     * @param  StoreCategoryGroupRequest  $request
     * @return CategoryGroupData
     */
    public static function fromRequest(StoreCategoryGroupRequest $request): CategoryGroupData
    {
        return new self(
            name: $request->validated('name'),
            notes: $request->validated('notes'),
            ledger: $request->ledger,
            order: null
        );
    }
}
