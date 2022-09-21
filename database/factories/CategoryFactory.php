<?php

namespace Database\Factories;

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
            'name' => $this->faker->word,
            'notes' => $this->faker->sentence,
            'order' => 0
        ];
    }

    public function hidden(): CategoryFactory
    {
        return $this->state(fn () => [
            'is_hidden' => true,
        ]);
    }
}
