<?php

namespace Database\Factories;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inflow'           => $this->faker->randomFloat(2, 1, 999999),
            'outflow'          => $this->faker->randomFloat(2, 1, 999999),
            'remarks'          => $this->faker->sentence,
            'transaction_date' => $this->faker->date,
        ];
    }

    public function approved(): TransactionFactory
    {
        return $this->state(fn () => [
            'is_approved' => true,
            'approved_at' => Carbon::now(),
        ]);
    }

    public function rejected(): TransactionFactory
    {
        return $this->state(fn () => [
            'rejected_at' => Carbon::now(),
            'deleted_at'  => Carbon::now(),
        ]);
    }

    public function cleared(): TransactionFactory
    {
        return $this->state(fn () => [
            'is_approved' => true,
            'is_cleared'  => true,
            'cleared_at'  => Carbon::now(),
            'approved_at' => Carbon::now(),
        ]);
    }

    public function uncleared(): TransactionFactory
    {
        return $this->state(fn () => [
            'is_approved' => false,
            'is_cleared'  => false,
            'cleared_at'  => null,
            'approved_at' => null,
        ]);
    }

    public function excluded(): TransactionFactory
    {
        return $this->state(fn () => [
            'is_excluded' => true,
        ]);
    }

    public function setOutflow(float $amount = null): TransactionFactory
    {
        return $this->state(fn () => [
            'inflow'  => null,
            'outflow' => $amount ?? $this->faker->randomFloat(2, 1, 999999),
        ]);
    }

    public function setInflow(float $amount = null): TransactionFactory
    {
        return $this->state(fn () => [
            'inflow'  => $amount ?? $this->faker->randomFloat(2, 1, 999999),
            'outflow' => null,
        ]);
    }
}
