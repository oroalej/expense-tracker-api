<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class TransactionFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
			'amount' => $this->faker->numberBetween(),
			'remarks' => $this->faker->sentence,
			'transaction_date' => $this->faker->date,
			'uuid' => $this->faker->uuid,
		];
	}
}
