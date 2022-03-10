<?php

namespace Database\Factories;

use App\Enums\CategoryTypeState;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class CategoryFactory extends Factory
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
			'category_type' => $this->faker->randomElement(
				CategoryTypeState::getValues()
			),
		];
	}

	public function setCategoryType(
		CategoryTypeState $categoryType
	): CategoryFactory {
		return $this->state(function () use ($categoryType) {
			return [
				'category_type' => $categoryType->value,
			];
		});
	}
}
