<?php

namespace Tests\Feature\Transaction\Transfer;

use App\Enums\DefaultCategoryIDs;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class ApprovalTransferTransactionTest extends TestCase
{
    public Account  $account;
    public Account  $transferAccount;
    public Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::find(DefaultCategoryIDs::TRANSFER->value);
        $this->account  = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(0)
            ->create();

        $this->transferAccount = Account::factory()
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
            ->for($this->transferAccount, 'transfer')
            ->setAmount(50000)
            ->uncleared()
            ->create();

        $transactionId = Hashids::encode($transaction->id);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 0
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->transferAccount->id,
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

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->transferAccount->id,
            'current_balance' => 0
        ]);
    }

    public function test_assert_account_current_balance_will_be_updated_when_cleared_transaction_is_approved(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->ledger)
            ->for($this->category)
            ->for($this->account, 'account')
            ->for($this->transferAccount, 'transfer')
            ->setAmount(50000)
            ->unapproved()
            ->create();

        $transactionId = Hashids::encode($transaction->id);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 0
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->transferAccount->id,
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

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->transferAccount->id,
            'current_balance' => 50000
        ]);
    }
}
