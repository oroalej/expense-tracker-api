<?php

namespace Database\Factories;

use App\Models\Debt;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Debt>
 */
class DebtFactory extends Factory
{
    protected $model = Debt::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currentBalance = $this->faker->randomFloat(2, 1, 999999);

        return [
            'name' => $this->faker->word,
            'notes' => $this->faker->sentence,
            'current_balance' => $currentBalance,
            'interest_rate' => $this->faker->randomFloat(2, 1, 20),
            'min_payment_amount' => $currentBalance / 12,
        ];
    }

    public function closed(): DebtFactory
    {
        return $this->state(fn () => [
            'is_closed' => true,
            'closed_at' => Carbon::now(),
        ]);
    }
}
