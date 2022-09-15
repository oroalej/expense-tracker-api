<?php

namespace Database\Factories;

use App\Models\Ledger;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class LedgerFactory extends Factory
{
    protected $model = Ledger::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'uuid' => $this->faker->uuid,
        ];
    }

    public function archived(): LedgerFactory
    {
        return $this->state(fn () => [
            'is_archived' => true,
            'archived_at' => Carbon::now(),
        ]);
    }
}
