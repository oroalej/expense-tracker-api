<?php

namespace Database\Seeders;

use App\DTO\CategoryData;
use App\Enums\CategoryTypeState;
use App\Enums\DefaultCategoryIDs;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Database\Seeder;

class DefaultCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->iterate($this->getIncomeData(), CategoryTypeState::INCOME);
        $this->iterate($this->getExpenseData(), CategoryTypeState::EXPENSE);
        $this->iterate($this->getOthersData(), CategoryTypeState::OTHERS);
    }

    public function iterate(array $data, CategoryTypeState $type, Category $parent = null)
    {
        foreach ($data as $index => $item) {
            if (! is_array($item)) {
                $item['name'] = $item;
            }

            $category = (new CategoryService())->store(
                new CategoryData(
                    name: $item['name'],
                    category_type: $type,
                    parent: $parent,
                    is_visible: $item['is_visible'] ?? true,
                    is_budgetable: $item['is_budgetable'] ?? true,
                    is_reportable: $item['is_reportable'] ?? true,
                    is_editable: false,
                    order: $item['order'] ?? $index + 1,
                    id: $item['id'] ?? null
                )
            );

            if (array_key_exists('children', $item)) {
                $this->iterate($item['children'], $type, $category);
            }
        }
    }

    public function getIncomeData(): array
    {
        return [
            [
                'name'  => 'Others',
                'order' => 99999
            ]
        ];
    }


    public function getExpenseData(): array
    {
        return [
            [
                'name'  => 'Others',
                'order' => 99999
            ]
        ];
    }

    public function getOthersData(): array
    {
        return [
            [
                'id'   => DefaultCategoryIDs::DEBT->value,
                'name' => 'Debt'
            ], [
                'id'   => DefaultCategoryIDs::LOAN->value,
                'name' => 'Loan'
            ], [
                'id'   => DefaultCategoryIDs::TRANSFER->value,
                'name' => 'Transfer'
            ]
        ];
    }
}
