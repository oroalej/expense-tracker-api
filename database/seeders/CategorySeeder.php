<?php

namespace Database\Seeders;

use App\Actions\Category\CreateCategory;
use App\DataTransferObjects\CategoryData;
use App\Enums\CategoryTypeState;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    protected User $user;

    /**
     * Run the database seeds.
     *
     * @param  User|Authenticatable  $user
     * @return void
     */
    public function run(User|Authenticatable $user): void
    {
        $this->user = $user;

        $this->dataCategories(
            $this->getIncomeCategories(),
            CategoryTypeState::Income
        );
        $this->dataCategories(
            $this->getExpenseCategories(),
            CategoryTypeState::Expense
        );
    }

    /**
     * @param  array  $categories
     * @param  CategoryTypeState  $categoryType
     * @param  int|null  $parentId
     * @return void
     */
    public function dataCategories(
        array $categories,
        CategoryTypeState $categoryType,
        int|null $parentId = null
    ): void {
        foreach ($categories as $data) {
            $attributes['name'] = $data['name'] ?? $data;
            $attributes['is_editable'] = $data['is_editable'] ?? true;

            $categoryData = new CategoryData(
                name: $attributes['name'],
                category_type_id: 1,
                order: 0,
                category_group_id: $parentId,
                is_editable: $attributes['is_editable']
            );

            $category = (new CreateCategory(
                $categoryData,
                $this->user
            ))->execute();

            if (isset($data['children'])) {
                $this->dataCategories(
                    $data['children'],
                    $categoryType,
                    $category->id
                );
            }
        }
    }

    public function getIncomeCategories(): array
    {
        return [
            'Award',
            'Gifts',
            'Interest Money',
            'Others',
            'Salary',
            'Selling',
            [
                'name' => 'Deposit',
                'is_editable' => false,
            ],
        ];
    }

    public function getExpenseCategories(): array
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
        ];
    }
}
