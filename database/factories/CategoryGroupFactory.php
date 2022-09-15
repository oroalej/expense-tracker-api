<?php

namespace Database\Factories;

use App\Models\CategoryGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class CategoryGroupFactory extends Factory
{
    protected $model = CategoryGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'notes' => $this->faker->sentence,
        ];
    }

    public function hidden(): CategoryGroupFactory
    {
        return $this->state(fn () => [
            'is_hidden' => true,
        ]);
    }
}
