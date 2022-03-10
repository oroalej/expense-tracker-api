<?php

namespace Database\Factories;

use App\Enums\WalletTypeState;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory=Wallet>
 */
class WalletFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
			'name' => $this->faker->word,
			'description' => $this->faker->sentence,
			'current_balance' => $this->faker->numberBetween(),
			'wallet_type' => $this->faker->randomElement(
				WalletTypeState::getValues()
			),
		];
	}

	public function setWalletType(WalletTypeState $walletType): WalletFactory
	{
		return $this->state(function () use ($walletType) {
			return [
				'wallet_type' => $walletType->value,
			];
		});
	}
}
