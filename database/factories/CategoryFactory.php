<?php

namespace Database\Factories;

use App\Enums\CategoryTypeState;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'          => $this->faker->word,
            'notes'         => $this->faker->sentence,
            'order'         => 1,
            'is_visible'    => true,
            'is_budgetable' => true,
            'is_reportable' => true
        ];
    }

    public function incomeType(): CategoryFactory
    {
        return $this->state(fn () => [
            'category_type' => CategoryTypeState::INCOME->value,
        ]);
    }

    public function expenseType(): CategoryFactory
    {
        return $this->state(fn () => [
            'category_type' => CategoryTypeState::EXPENSE->value,
        ]);
    }

    public function othersType(): CategoryFactory
    {
        return $this->state(fn () => [
            'category_type' => CategoryTypeState::OTHERS->value,
        ]);
    }

    public function visible(bool $status = true): CategoryFactory
    {
        return $this->state(fn () => [
            'is_visible' => $status,
        ]);
    }

    public function budgetable(bool $status = true): CategoryFactory
    {
        return $this->state(fn () => [
            'is_budgetable' => $status,
        ]);
    }

    public function reportable(bool $status = true): CategoryFactory
    {
        return $this->state(fn () => [
            'is_reportable' => $status,
        ]);
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Category $category) {
            $category->order = Category::getLastOrder(
                ledgerId: $category->ledger_id,
                categoryType: $category->category_type->value,
                parentId: $category->parent_id
            );
        });
    }
}
