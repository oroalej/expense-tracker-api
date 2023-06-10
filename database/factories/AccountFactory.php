<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'current_balance' => $this->faker->numberBetween(0, 999999),
        ];
    }

    public function archived(): AccountFactory
    {
        return $this->state(fn () => [
            'is_archived' => true,
            'archived_at' => now(),
        ]);
    }
}
