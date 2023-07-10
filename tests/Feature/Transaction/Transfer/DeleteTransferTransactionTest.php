<?php

namespace Tests\Feature\Transaction\Transfer;

use App\Enums\DefaultCategoryIDs;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class DeleteTransferTransactionTest extends TestCase
{
    public function test_assert_account_balance_was_reverted_when_transfer_transaction_is_approved_and_cleared()
    {
        $transferCategory = Category::find(DefaultCategoryIDs::TRANSFER->value);

        /** @var Account $account */
        $account = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(50000)
            ->create();

        /** @var Account $transferAccount */
        $transferAccount = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(0)
            ->create();

        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->ledger)
            ->for($account)
            ->for($transferCategory)
            ->for($transferAccount, 'transfer')
            ->setAmount(50000)
            ->cleared()
            ->create();

        $transactionId = Hashids::encode($transaction->id);

        $this->assertDatabaseHas('accounts', [
            'id'              => $account->id,
            'current_balance' => 0
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $transferAccount->id,
            'current_balance' => 50000
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson("api/transactions/$transactionId")
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $account->id,
            'current_balance' => 50000
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $transferAccount->id,
            'current_balance' => 0
        ]);
    }
}
