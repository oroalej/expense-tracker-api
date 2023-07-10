<?php

namespace Tests\Feature\Transaction\Income;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class DeleteIncomeTransactionTest extends TestCase
{
    public Transaction $transaction;
    public Account     $account;

    protected function setUp(): void
    {
        parent::setUp();

        $incomeCategory = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $this->account = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(0)
            ->create();

        $this->transaction = Transaction::factory()
            ->for($this->ledger)
            ->for($this->account)
            ->for($incomeCategory)
            ->setAmount(50000)
            ->cleared()
            ->create();

        $transactionId = Hashids::encode($this->transaction->id);

        $this->url = "api/transactions/$transactionId";
    }

    public function test_assert_account_balance_was_reverted_when_income_transaction_is_approved_and_cleared()
    {
        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 50000
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url)
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 0
        ]);
    }
}
