<?php

namespace Tests\Feature\Transaction\Expense;

use App\Models\Account;
use App\Models\BudgetCategory;
use App\Models\Category;
use App\Models\Transaction;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class ApprovalExpenseTransactionTest extends TestCase
{
    public Account  $account;
    public Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()
            ->for($this->ledger)
            ->expenseType()
            ->create();

        $this->account = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(0)
            ->create();
    }

    public function test_assert_nothing_happens_when_uncleared_transaction_was_approved(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->ledger)
            ->for($this->account)
            ->for($this->category)
            ->setAmount(50000)
            ->uncleared()
            ->create();

        $transactionId = Hashids::encode($transaction->id);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 0
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/transactions/$transactionId/approved")
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 0
        ]);
    }

    public function test_assert_account_current_balance_will_update_when_cleared_transaction_was_approved(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->ledger)
            ->for($this->account)
            ->for($this->category)
            ->setAmount(50000)
            ->unapproved()
            ->create();

        $transactionId = Hashids::encode($transaction->id);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 0
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/transactions/$transactionId/approved")
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => -50000
        ]);
    }

//    public function test_assert_rejected_transaction_will_be_deleted(): void
//    {
//        /** @var Transaction $transaction */
//        $transaction = Transaction::factory()
//            ->for($this->ledger)
//            ->for($this->account)
//            ->for($this->category)
//            ->setAmount(50000)
//            ->unapproved()
//            ->create();
//
//        $transactionId  = Hashids::encode($transaction->id);
//
//        $this->actingAs($this->user)
//            ->appendHeaderLedgerId()
//            ->postJson("api/transactions/$transactionId/rejected")
//            ->assertOk();
//
//        $transaction->refresh();
//
//        $this->assertSoftDeleted($transaction);
//        $this->assertNotNull($transaction->rejected_at);
//    }

    public function test_assert_budget_category_reverted_when_transaction_was_rejected(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->ledger)
            ->for($this->account)
            ->for($this->category)
            ->setAmount(50000)
            ->unapproved()
            ->create();

        $transactionId  = Hashids::encode($transaction->id);
        $budgetCategory = BudgetCategory::getByTransaction($transaction);

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $budgetCategory->id,
            'activity'  => -50000,
            'available' => -50000
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/transactions/$transactionId/rejected")
            ->assertOk();

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $budgetCategory->id,
            'activity'  => 0,
            'available' => 0
        ]);
    }
}
