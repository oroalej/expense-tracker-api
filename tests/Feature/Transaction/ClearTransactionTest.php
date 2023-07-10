<?php

namespace Tests\Feature\Transaction;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class ClearTransactionTest extends TestCase
{
    public Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->create();
    }

    public function test_cleared_transaction_will_automatically_be_approved()
    {
        $category = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($category)
            ->for($this->ledger)
            ->uncleared()
            ->create();

        $transactionId = Hashids::encode($transaction->id);

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'is_approved' => false,
            'approved_at' => null,
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/transactions/$transactionId/cleared")
            ->assertOk();

        $transaction->refresh();

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'is_approved' => true,
            'is_cleared'  => true
        ]);
        $this->assertNotNull($transaction->cleared_at);
        $this->assertNotNull($transaction->approved_at);
    }
}
