<?php

namespace Tests\Feature\Transaction;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class RejectTransactionTest extends TestCase
{
    public function test_rejected_transaction_will_be_deleted(): void
    {
        $account = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->create();

        /** @var Category $expenseCategory */
        $expenseCategory = Category::factory()
            ->for($this->ledger)
            ->expenseType()
            ->create();

        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($account)
            ->for($expenseCategory)
            ->for($this->ledger)
            ->unapproved()
            ->create();

        $transactionId = Hashids::encode($transaction->id);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/transactions/$transactionId/rejected")
            ->assertOk();

        $transaction->refresh();

        $this->assertNotNull($transaction->rejected_at);
        $this->assertSoftDeleted($transaction);
    }
}
