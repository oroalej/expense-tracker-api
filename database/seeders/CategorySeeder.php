<?php

namespace Database\Seeders;

use App\Actions\Category\CreateCategoryAction;
use App\Actions\CategoryGroup\CreateCategoryGroupAction;
use App\DataTransferObjects\CategoryData;
use App\DataTransferObjects\CategoryGroupData;
use App\Models\Ledger;
use Illuminate\Database\Seeder;
use Throwable;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param  Ledger  $ledger
     * @return void
     * @throws Throwable
     */
    public function run(Ledger $ledger): void
    {
        foreach ($this->getData() as $index =>$group) {
            $categoryGroup = (new CreateCategoryGroupAction())->execute(
                new CategoryGroupData(
                    name: $group['name'],
                    notes: null,
                    order: $index + 1,
                    ledger: $ledger
                )
            );

            foreach ($group['children'] as $order => $categoryName) {
                (new CreateCategoryAction())->execute(
                    new CategoryData(
                        name: $categoryName,
                        notes: null,
                        order: $order + 1,
                        categoryGroup: $categoryGroup
                    )
                );
            }
        }
    }

    public function getData(): array
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
            ],
            [
                'name'     => 'Transportation',
                'children' => [
                    'Fare',
                    'Maintenance',
                    'Parking Fees',
                    'Petrol',
                    'Taxi',
                ],
            ],
            [
                'name'     => 'Shopping',
                'children' => [
                    'Clothing',
                    'Footwear',
                    'Accessories',
                    'Electronics',
                ]
            ]
        ];
    }
}
