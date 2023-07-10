<?php

namespace Tests\Feature\Transaction\Income;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class ClearIncomeTransactionTest extends TestCase
{
    public Account  $account;
    public Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $this->account = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(0)
            ->create();
    }

    public function test_assert_account_current_balance_will_be_updated_when_the_transaction_is_cleared(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->ledger)
            ->for($this->account)
            ->for($this->category)
            ->setAmount(50000)
            ->approved()
            ->create();

        $transactionId = Hashids::encode($transaction->id);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 0
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/transactions/$transactionId/cleared")
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 50000
        ]);
    }

    public function test_assert_account_current_balance_is_reverted_when_the_transaction_is_uncleared(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->ledger)
            ->for($this->account)
            ->for($this->category)
            ->setAmount(50000)
            ->cleared()
            ->create();

        $transactionId = Hashids::encode($transaction->id);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 50000
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/transactions/$transactionId/uncleared")
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 0
        ]);
    }
}
