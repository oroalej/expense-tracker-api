<?php

namespace App\DTO;

use App\Enums\CategoryTypeState;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\Ledger;

class CategoryData
{
    public function __construct(
        public readonly string $name,
        public readonly CategoryTypeState $category_type,
        public readonly ?Ledger $ledger = null,
        public readonly ?Category $parent = null,
        public readonly ?string $notes = "",
        public readonly ?bool $is_visible = true,
        public readonly ?bool $is_budgetable = true,
        public readonly ?bool $is_reportable = true,
        public readonly ?bool $is_editable = true,
        public readonly ?int $order = null,
        public readonly ?int $id = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'name'          => $this->name,
            'notes'         => $this->notes,
            'order'         => $this->order,
            'category_type' => $this->category_type->value,
            'is_visible'    => $this->is_visible,
            'is_budgetable' => $this->is_budgetable,
            'is_reportable' => $this->is_reportable,
        ];
    }

    public static function fromRequest(StoreCategoryRequest|UpdateCategoryRequest $request): CategoryData
    {
        $parent = null;

        if ($request->validated('parent_id')) {
            $parent = Category::find($request->validated('parent_id'));
        }

        return new static(
            name: $request->validated('name'),
            ledger: $request->ledger,
            category_type: CategoryTypeState::from($request->validated('category_type')),
            parent: $parent,
            notes: $request->validated('notes'),
            is_visible: $request->validated('is_visible') ?? true,
            is_budgetable: $request->validated('is_budgetable') ?? true,
            is_reportable: $request->validated('is_reportable') ?? true,
            order: $request->validated('order'),
        );
    }
}
