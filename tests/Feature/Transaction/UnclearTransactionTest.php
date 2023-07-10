<?php

namespace Tests\Feature\Transaction;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class UnclearTransactionTest extends TestCase
{
    public Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(0)
            ->create();
    }

    public function test_transaction_automatically_approved_when_uncleared()
    {
        $incomeCategory = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($incomeCategory)
            ->for($this->ledger)
            ->state([
                'is_approved' => false,
                'is_cleared'  => true,
            ])
            ->create();

        $transactionId = Hashids::encode($transaction->id);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/transactions/$transactionId/uncleared")
            ->assertOk();

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'is_cleared'  => false,
            'is_approved' => true,
        ]);

        $transaction->refresh();

        $this->assertNotNull($transaction->approved_at);
    }
}
