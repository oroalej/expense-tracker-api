<?php

namespace Tests\Feature\Transaction;

use App\Enums\AccountTypeState;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\BudgetCategory;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Transaction;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class UpdateBudgetTransactionTest extends TestCase
{
    public Transaction   $transaction;
    public Account       $account;
    public Category      $category;
    public CategoryGroup $categoryGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()
            ->for($this->ledger)
            ->for(AccountType::find(AccountTypeState::Cash->value))
            ->create();

        $this->categoryGroup = CategoryGroup::factory()
            ->for($this->ledger)
            ->create();

        $this->category = Category::factory()
            ->for($this->categoryGroup)
            ->for($this->ledger)
            ->create();
    }

    public function test_budget_category_is_correctly_adjusted_when_category_id_was_changed(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->category)
            ->for($this->ledger)
            ->for($this->account)
            ->setInflow(1000)
            ->create();

        /** @var Category $newCategory */
        $newCategory = Category::factory()
            ->for($this->categoryGroup)
            ->for($this->ledger)
            ->create();

        $hashTransactionId = Hashids::encode($transaction->id);
        $url               = "api/transactions/$hashTransactionId";

        $oldBudgetCategory = BudgetCategory::getByTransaction($transaction);

        $attributes = [
            'inflow'           => $transaction->inflow,
            'outflow'          => $transaction->outflow,
            'remarks'          => $transaction->remarks,
            'transaction_date' => $transaction->transaction_date->format('Y-m-d'),
            'category_id'      => Hashids::encode($newCategory->id),
            'account_id'       => Hashids::encode($this->account->id),
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $oldBudgetCategory->id,
            'activity'  => $oldBudgetCategory->activity - 1000,
            'available' => $oldBudgetCategory->available - 1000
        ]);

        $transaction->refresh();

        $newBudgetCategory = BudgetCategory::getByTransaction($transaction);

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $newBudgetCategory->id,
            'activity'  => 1000,
            'available' => 1000
        ]);
    }

    public function test_budget_category_is_correctly_adjusted_when_transaction_was_changed_to_different_month_and_year(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->category)
            ->for($this->ledger)
            ->for($this->account)
            ->setInflow(2000)
            ->create();

        /** @var Category $newCategory */
        $newCategory = Category::factory()
            ->for($this->categoryGroup)
            ->for($this->ledger)
            ->create();

        $hashTransactionId = Hashids::encode($transaction->id);
        $url               = "api/transactions/$hashTransactionId";

        $oldBudgetCategory = BudgetCategory::getByTransaction($transaction);

        $attributes = [
            'inflow'           => $transaction->inflow,
            'outflow'          => $transaction->outflow,
            'remarks'          => $transaction->remarks,
            'transaction_date' => $this->faker->dateTimeThisYear('4 months')->format('Y-m-d'),
            'category_id'      => Hashids::encode($newCategory->id),
            'account_id'       => Hashids::encode($this->account->id),
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $oldBudgetCategory->id,
            'activity'  => $oldBudgetCategory->activity - 2000,
            'available' => $oldBudgetCategory->available - 2000
        ]);

        $transaction->refresh();

        $newBudgetCategory = BudgetCategory::getByTransaction($transaction);

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $newBudgetCategory->id,
            'activity'  => 2000,
            'available' => 2000
        ]);
    }

    public function test_budget_category_correctly_is_adjusted_when_inflow_was_changed(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->category)
            ->for($this->ledger)
            ->for($this->account)
            ->setInflow(3000)
            ->create();

        $hashTransactionId = Hashids::encode($transaction->id);
        $url               = "api/transactions/$hashTransactionId";

        $attributes = [
            'inflow'           => 10000,
            'outflow'          => $transaction->outflow,
            'remarks'          => $transaction->remarks,
            'transaction_date' => $transaction->transaction_date->format('Y-m-d'),
            'category_id'      => Hashids::encode($this->category->id),
            'account_id'       => Hashids::encode($this->account->id),
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($url, $attributes)
            ->assertOk();

        $budgetCategory = BudgetCategory::getByTransaction($transaction);

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $budgetCategory->id,
            'activity'  => $attributes['inflow'],
            'available' => $attributes['inflow']
        ]);
    }

    public function test_budget_category_is_correctly_adjusted_when_outflow_was_changed(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->category)
            ->for($this->ledger)
            ->for($this->account)
            ->setOutflow(3000)
            ->create();

        $hashTransactionId = Hashids::encode($transaction->id);
        $url = "api/transactions/$hashTransactionId";

        $attributes = [
            'inflow'           => $transaction->inflow,
            'outflow'          => 5000,
            'remarks'          => $transaction->remarks,
            'transaction_date' => $transaction->transaction_date->format('Y-m-d'),
            'category_id'      => Hashids::encode($this->category->id),
            'account_id'       => Hashids::encode($this->account->id),
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($url, $attributes)
            ->assertOk();

        $budgetCategory = BudgetCategory::getByTransaction($transaction);

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $budgetCategory->id,
            'activity'  => -5000,
            'available' => -5000
        ]);
    }
}
