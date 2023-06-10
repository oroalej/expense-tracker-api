<?php

namespace Database\Factories;

use App\Models\BudgetCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BudgetCategory>
 */
class BudgetCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assigned'  => $this->faker->randomNumber(),
            'available' => $this->faker->randomNumber(),
            'activity'  => $this->faker->randomNumber()
        ];
    }

    public function zeroValues(): BudgetCategoryFactory
    {
        return $this->state(fn () => [
            'assigned'  => 0,
            'available' => 0,
            'activity'  => 0
        ]);
    }
}
