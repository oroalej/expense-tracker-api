<?php

namespace Database\Factories;

use App\Models\BudgetCategory;
use App\Models\Transaction;
use App\Services\AccountService;
use App\Services\BudgetCategoryService;
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
        $inflow     = $this->faker->boolean;
        $isCleared  = $this->faker->boolean;
        $isApproved = $this->faker->boolean;
        $date       = $this->faker->dateTimeThisYear('4 months');

        return [
            'inflow'           => $inflow ? $this->faker->numberBetween(0, 200000) : 0,
            'outflow'          => $inflow ? 0 : $this->faker->numberBetween(0, 200000),
            'remarks'          => $this->faker->sentence,
            'transaction_date' => $date,
            'is_cleared'       => $isCleared,
            'cleared_at'       => $isCleared ? Carbon::now() : null,
            'is_approved'      => $isApproved || $isCleared,
            'approved_at'      => $isApproved || $isCleared ? Carbon::now() : null
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

    public function setOutflow(int $amount = 0): TransactionFactory
    {
        return $this->state(fn () => [
            'outflow' => $amount ?? $this->faker->numberBetween(0, 200000),
            'inflow'  => 0
        ]);
    }

    public function setInflow(int $amount = 0): TransactionFactory
    {
        return $this->state(fn () => [
            'outflow' => 0,
            'inflow'  => $amount ?? $this->faker->numberBetween(0, 200000),
        ]);
    }

    public function configure(): TransactionFactory
    {
        return $this->afterCreating(static function (Transaction $transaction) {
            (new BudgetCategoryService())->adjustActivity(
                budgetCategory: BudgetCategory::getByTransaction($transaction),
                inflow: $transaction->inflow,
                outflow: $transaction->outflow,
            );

            if ($transaction->is_cleared & $transaction->is_approved) {
                (new AccountService())->adjustAccountBalance(
                    account: $transaction->account,
                    inflow: $transaction->inflow,
                    outflow: $transaction->outflow
                );
            }
        });
    }
}
