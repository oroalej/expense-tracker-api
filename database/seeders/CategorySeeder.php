<?php

namespace Database\Seeders;

use App\DTO\CategoryData;
use App\Enums\CategoryTypeState;
use App\Models\Category;
use App\Models\Ledger;
use App\Services\CategoryService;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function __construct(readonly protected Ledger $ledger)
    {
    }

    public function run(): void
    {
        $this->iterate($this->getIncomeData(), CategoryTypeState::INCOME);
        $this->iterate($this->getExpenseData(), CategoryTypeState::EXPENSE);
    }

    public function iterate(array $data, CategoryTypeState $type, Category $parent = null)
    {
        foreach ($data as $index => $item) {
            $name = $item;

            if (is_array($item)) {
                $name = $item['name'];
            }

            $category = (new CategoryService())->store(
                new CategoryData(
                    name: $name,
                    ledger: $this->ledger,
                    category_type: $type,
                    parent: $parent,
                    is_editable: $item['is_editable'] ?? true,
                    order: $index + 1
                )
            );

            if (is_array($item) && array_key_exists('children', $item)) {
                $this->iterate($item['children'], $type, $category);
            }
        }
    }

    public function getIncomeData(): array
    {
        return [
            'Salary',
            'Gift',
            'Goal',
            'Business',
            'Commission',
            'Interest',
            'Investment',
            'Selling'
        ];
    }

    public function getExpenseData(): array
    {
        return [
            [
                'name'     => 'Bills & Utilities',
                'children' => [
                    'Electricity',
                    'Gas',
                    'Internet',
                    'Phone',
                    'Rentals',
                    'Television',
                    'Water',
                ],
            ], [
                'name'     => 'Transportation',
                'children' => [
                    'Fare',
                    'Maintenance',
                    'Parking Fees',
                    'Petrol',
                    'Taxi',
                ],
            ], [
                'name'     => 'Shopping',
                'children' => [
                    'Clothing',
                    'Footwear',
                    'Accessories',
                    'Electronics',
                    'Grocery'
                ],
            ]
        ];
    }
}
