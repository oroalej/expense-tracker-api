<?php

namespace Tests\Feature\Transaction;

use App\Enums\AccountTypeState;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Transaction;
use Tests\TestCase;

class UpdateAccountTransactionTest extends TestCase
{
    public Account $account;

    public Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $cashAccountType = AccountType::find(AccountTypeState::Cash->value);
        $this->account   = Account::factory()
            ->for($this->ledger)
            ->for($cashAccountType)
            ->create();

        $this->category = Category::factory()
            ->for(
                CategoryGroup::factory()->for($this->ledger)
            )
            ->create();
    }

    public function test_account_balance_is_correct_after_outflow_value_is_updated(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($this->category)
            ->setOutflow()
            ->create();

        $url = "api/accounts/{$this->account->uuid}/transactions/$transaction->uuid";

        $attributes = [
            'outflow'          => $this->faker->randomFloat(2, 1, 999999),
            'remarks'          => $transaction->remarks,
            'transaction_date' => $transaction->transaction_date->format('Y-m-d'),
            'category_id'      => $transaction->category->uuid,
            'account_id'       => $this->account->uuid,
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $this->account->current_balance +
                $transaction->outflow -
                $attributes['outflow'],
        ]);
    }

    public function test_account_balance_is_correct_after_inflow_value_is_updated(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($this->category)
            ->setInflow()
            ->create();

        $url = "api/accounts/{$this->account->uuid}/transactions/$transaction->uuid";

        $attributes = [
            'inflow'           => $this->faker->randomFloat(2, 1, 999999),
            'remarks'          => $transaction->remarks,
            'transaction_date' => $transaction->transaction_date->format(
                'Y-m-d'
            ),
            'category_id'      => $transaction->category->uuid,
            'account_id'       => $this->account->uuid,
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $this->account->current_balance -
                $transaction->inflow +
                $attributes['inflow'],
        ]);
    }

    public function test_account_balance_is_correct_after_inflow_and_outflow_values_are_updated(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($this->category)
            ->create();

        $url = "api/accounts/{$this->account->uuid}/transactions/$transaction->uuid";

        $attributes = [
            'inflow'           => $this->faker->randomFloat(2, 1, 999999),
            'outflow'          => $this->faker->randomFloat(2, 1, 999999),
            'remarks'          => $transaction->remarks,
            'transaction_date' => $transaction->transaction_date->format(
                'Y-m-d'
            ),
            'category_id'      => $transaction->category->uuid,
            'account_id'       => $this->account->uuid,
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($url, $attributes)
            ->assertOk();

        $expectedAccountBalance =
            $this->account->current_balance +
            ($transaction->outflow + $attributes['inflow']) -
            ($transaction->inflow + $attributes['outflow']);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $expectedAccountBalance,
        ]);
    }
}
