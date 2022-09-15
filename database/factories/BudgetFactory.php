<?php

namespace Database\Factories;

use App\Models\Budget;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $today = now();

        return [
            'notes' => $this->faker->sentence,
            'month' => $today->month,
            'year' => $today->year,
        ];
    }

    public function configure(): BudgetFactory
    {
        return $this->afterCreating(static function (Budget $budget) {
        });
    }
}
