<?php

namespace Database\Seeders;

use App\DTO\CategoryData;
use App\DTO\CategoryGroupData;
use App\Models\Ledger;
use App\Services\CategoryGroupService;
use App\Services\CategoryService;
use Illuminate\Database\Seeder;
use Throwable;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param  Ledger  $ledger
     * @return void
     *
     * @throws Throwable
     */
    public function run(Ledger $ledger): void
    {
        foreach ($this->getData() as $index => $group) {
            $categoryGroup = (new CategoryGroupService())->store(
                new CategoryGroupData(
                    name: $group['name'],
                    notes: null,
                    ledger: $ledger,
                    order: $index + 1
                )
            );

            foreach ($group['children'] as $order => $categoryName) {
                (new CategoryService())->store(
                    new CategoryData(
                        name: $categoryName,
                        category_group: $categoryGroup,
                        ledger: $ledger,
                        notes: null,
                        order: $order + 1
                    )
                );
            }
        }
    }

    public function getData(): array
    {
        return [
            [
                'name' => 'Bills & Utilities',
                'children' => [
                    'Electricity',
                    'Gas',
                    'Internet',
                    'Phone',
                    'Rentals',
                    'Television',
                    'Water',
                ],
            ],
            [
                'name' => 'Transportation',
                'children' => [
                    'Fare',
                    'Maintenance',
                    'Parking Fees',
                    'Petrol',
                    'Taxi',
                ],
            ],
            [
                'name' => 'Shopping',
                'children' => [
                    'Clothing',
                    'Footwear',
                    'Accessories',
                    'Electronics',
                ],
            ],
        ];
    }
}
