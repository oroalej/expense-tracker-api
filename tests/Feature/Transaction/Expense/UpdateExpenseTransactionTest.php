<?php

namespace Tests\Feature\Transaction\Expense;

use App\Enums\DefaultCategoryIDs;
use App\Models\Account;
use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\Category;
use App\Models\Transaction;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class UpdateExpenseTransactionTest extends TestCase
{
    protected Account     $account;
    protected Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Account $account */
        $this->account = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(0)
            ->create();

        $this->transaction = Transaction::factory()
            ->for(
                Category::factory()
                    ->for($this->ledger)
                    ->expenseType()
            )
            ->for($this->ledger)
            ->for($this->account)
            ->setAmount(2000)
            ->cleared()
            ->create();

        $transactionId = Hashids::encode($this->transaction->id);
        $this->url     = "api/transactions/$transactionId";
    }

    public function test_assert_account_current_balance_is_updated_when_amount_is_changed()
    {
        $attributes = [
            'amount'           => 5000,
            'remarks'          => $this->transaction->remarks,
            'transaction_date' => $this->transaction->transaction_date->toDateString(),
            'category_id'      => Hashids::encode($this->transaction->category_id),
            'account_id'       => Hashids::encode($this->transaction->account_id),
        ];

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => -2000
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => -5000
        ]);
    }

    public function test_assert_old_account_is_reverted_when_account_is_changed(): void
    {
        /** @var Account $anotherAccount */
        $anotherAccount = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(50)
            ->create();

        $attributes = [
            'amount'           => $this->transaction->amount,
            'remarks'          => $this->transaction->remarks,
            'transaction_date' => $this->transaction->transaction_date->toDateString(),
            'category_id'      => Hashids::encode($this->transaction->category_id),
            'account_id'       => Hashids::encode($anotherAccount->id),
        ];

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => -2000
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 0
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $anotherAccount->id,
            'current_balance' => -1950
        ]);
    }

    public function test_assert_old_account_is_reverted_when_account_and_amount_are_changed(): void
    {
        /** @var Account $anotherAccount */
        $anotherAccount = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(50)
            ->create();

        $attributes = [
            'amount'           => 4000,
            'remarks'          => $this->transaction->remarks,
            'transaction_date' => $this->transaction->transaction_date->toDateString(),
            'category_id'      => Hashids::encode($this->transaction->category_id),
            'account_id'       => Hashids::encode($anotherAccount->id),
        ];

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => -2000
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 0
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $anotherAccount->id,
            'current_balance' => -3950
        ]);
    }

    public function test_assert_budget_category_activity_and_available_are_updated_when_amount_is_changed()
    {
        $attributes = [
            'amount'           => 5000,
            'remarks'          => $this->transaction->remarks,
            'transaction_date' => $this->transaction->transaction_date->toDateString(),
            'category_id'      => Hashids::encode($this->transaction->category_id),
            'account_id'       => Hashids::encode($this->transaction->account_id),
        ];

        $budgetCategory = BudgetCategory::getByTransaction($this->transaction);

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $budgetCategory->id,
            'available' => -2000,
            'activity'  => -2000
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $budgetCategory->id,
            'available' => -5000,
            'activity'  => -5000
        ]);
    }

    public function test_assert_budget_category_changed_when_transaction_date_is_changed()
    {
        $transactionDate = $this->transaction->transaction_date->addMonths(2);
        $attributes      = [
            'amount'           => $this->transaction->amount,
            'remarks'          => $this->transaction->remarks,
            'transaction_date' => $transactionDate->toDateString(),
            'category_id'      => Hashids::encode($this->transaction->category_id),
            'account_id'       => Hashids::encode($this->transaction->account_id),
        ];

        $budget = Budget::getDataByDateAndLedger($transactionDate, $this->ledger);

        $budgetCategory    = BudgetCategory::getByTransaction($this->transaction);
        $newBudgetCategory = BudgetCategory::getOrCreateByBudgetAndCategory($budget, $this->transaction->category);

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $budgetCategory->id,
            'available' => -2000,
            'activity'  => -2000
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();


        $this->assertDatabaseHas('budget_categories', [
            'id'        => $budgetCategory->id,
            'available' => 0,
            'activity'  => 0
        ]);

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $newBudgetCategory->id,
            'available' => -2000,
            'activity'  => -2000
        ]);
    }

    public function test_assert_budget_category_changed_when_category_is_changed()
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->expenseType()
            ->create();

        $attributes = [
            'amount'           => $this->transaction->amount,
            'remarks'          => $this->transaction->remarks,
            'transaction_date' => $this->transaction->transaction_date->toDateString(),
            'category_id'      => Hashids::encode($category->id),
            'account_id'       => Hashids::encode($this->transaction->account_id),
        ];

        $budget = Budget::getDataByDateAndLedger($this->transaction->transaction_date, $this->ledger);

        $budgetCategory    = BudgetCategory::getByTransaction($this->transaction);
        $newBudgetCategory = BudgetCategory::getOrCreateByBudgetAndCategory($budget, $category);

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $budgetCategory->id,
            'available' => -2000,
            'activity'  => -2000
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $budgetCategory->id,
            'available' => 0,
            'activity'  => 0
        ]);

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $newBudgetCategory->id,
            'available' => -2000,
            'activity'  => -2000
        ]);
    }

    public function test_assert_budget_category_is_correct_when_category_amount_and_transaction_date_are_changed()
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->expenseType()
            ->create();

        $transactionDate = $this->transaction->transaction_date->addMonths(2);
        $attributes      = [
            'amount'           => 5000,
            'remarks'          => $this->transaction->remarks,
            'transaction_date' => $transactionDate->toDateString(),
            'category_id'      => Hashids::encode($category->id),
            'account_id'       => Hashids::encode($this->transaction->account_id),
        ];

        $budget = Budget::getDataByDateAndLedger($transactionDate, $this->ledger);

        $budgetCategory    = BudgetCategory::getByTransaction($this->transaction);
        $newBudgetCategory = BudgetCategory::getOrCreateByBudgetAndCategory($budget, $category);

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $budgetCategory->id,
            'available' => -2000,
            'activity'  => -2000
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $budgetCategory->id,
            'available' => 0,
            'activity'  => 0
        ]);

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $newBudgetCategory->id,
            'available' => -5000,
            'activity'  => -5000
        ]);
    }

    public function test_assert_budget_category_and_account_are_reverted_when_category_was_change_to_different_type()
    {
        /** @var Category $category */
        $category = Category::find(DefaultCategoryIDs::TRANSFER->value);

        /** @var Account $anotherAccount */
        $anotherAccount = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(300)
            ->create();

        $attributes = [
            'amount'           => $this->transaction->amount,
            'remarks'          => $this->transaction->remarks,
            'transaction_date' => $this->transaction->transaction_date->toDateString(),
            'category_id'      => Hashids::encode($category->id),
            'account_id'       => Hashids::encode($this->transaction->account_id),
            'transfer_id'      => Hashids::encode($anotherAccount->id),
        ];

        $budgetCategory = BudgetCategory::getByTransaction($this->transaction);

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $budgetCategory->id,
            'available' => -2000,
            'activity'  => -2000
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $budgetCategory->id,
            'available' => 0,
            'activity'  => 0
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => -2000
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $anotherAccount->id,
            'current_balance' => 2300
        ]);
    }
}
