<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Services\Transaction\Factory\TransactionFactory as TransactionStrategy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

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
            'remarks'          => $this->faker->sentence,
            'transaction_date' => $this->faker->dateTimeThisYear('4 months'),
            'amount'           => $this->faker->numberBetween(1, 200000),
        ];
    }

    public function approved(): TransactionFactory
    {
        return $this->state(fn () => [
            'is_approved' => true,
            'approved_at' => Carbon::now(),
            'is_cleared'  => false,
            'cleared_at'  => null,
        ]);
    }

    public function unapproved(): TransactionFactory
    {
        return $this->state(fn () => [
            'is_approved' => false,
            'approved_at' => null,
            'is_cleared'  => true,
            'cleared_at'  => Carbon::now(),
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

    public function setAmount(int $amount = 0): TransactionFactory
    {
        return $this->state(fn () => [
            'amount' => $amount ?? $this->faker->numberBetween(1, 200000),
        ]);
    }

    public function configure(): TransactionFactory
    {
        return $this->afterMaking(static function (Transaction $transaction) {
            $transaction->loadMissing('category');

            (TransactionStrategy::getStrategy($transaction->category))->store($transaction);
        });
    }
}
