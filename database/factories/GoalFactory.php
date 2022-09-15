<?php

namespace Database\Factories;

use App\Models\Goal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Goal>
 */
class GoalFactory extends Factory
{
    protected $model = Goal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $today = now();

        return [
            'name' => $this->faker->word,
            'notes' => $this->faker->sentence,
            'target_amount' => $this->faker->randomFloat(2, 1, 999999),
            'current_balance' => 0,
            'month' => $today->month,
            'year' => $today->year,
        ];
    }
}
