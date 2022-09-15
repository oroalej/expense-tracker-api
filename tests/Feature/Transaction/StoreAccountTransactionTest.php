<?php

namespace Tests\Feature\Transaction;

use App\Enums\AccountTypeState;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Category;
use App\Models\CategoryGroup;
use Tests\TestCase;

class StoreAccountTransactionTest extends TestCase
{
    public string $url;

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

        $this->url = "api/accounts/{$this->account->uuid}/transactions";
    }

    public function test_account_balance_is_deducted_when_outflow_is_filled(): void
    {
        $attributes = [
            'account_id'       => $this->account->uuid,
            'category_id'      => $this->category->uuid,
            'remarks'          => $this->faker->word,
            'outflow'          => $this->faker->numberBetween(1, 999999),
            'transaction_date' => $this->faker->date,
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $this->account->current_balance - $attributes['outflow'],
        ]);
    }

    public function test_account_balance_is_deducted_when_inflow_is_filled(): void
    {
        $attributes = [
            'account_id'       => $this->account->uuid,
            'category_id'      => $this->category->uuid,
            'remarks'          => $this->faker->word,
            'inflow'           => $this->faker->numberBetween(1, 999999),
            'transaction_date' => $this->faker->date,
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $this->account->current_balance + $attributes['inflow'],
        ]);
    }

    public function test_account_balance_is_not_deducted_when_transaction_is_not_approved(): void
    {
        $attributes = [
            'account_id'       => $this->account->uuid,
            'category_id'      => $this->category->uuid,
            'remarks'          => $this->faker->word,
            'inflow'           => $this->faker->numberBetween(1, 999999),
            'transaction_date' => $this->faker->date,
            'is_approved'      => false,
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $this->account->current_balance,
        ]);
    }

    public function test_account_balance_is_not_deducted_when_transaction_is_not_cleared(): void
    {
        $attributes = [
            'account_id'       => $this->account->uuid,
            'category_id'      => $this->category->uuid,
            'remarks'          => $this->faker->word,
            'inflow'           => $this->faker->numberBetween(1, 999999),
            'transaction_date' => $this->faker->date,
            'is_cleared'       => false,
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $this->account->current_balance,
        ]);
    }

    public function test_account_balance_is_not_deducted_when_transaction_is_not_cleared_and_approved(): void
    {
        $attributes = [
            'account_id'       => $this->account->uuid,
            'category_id'      => $this->category->uuid,
            'remarks'          => $this->faker->word,
            'inflow'           => $this->faker->numberBetween(1, 999999),
            'transaction_date' => $this->faker->date,
            'is_cleared'       => false,
            'is_approved'      => false,
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $this->account->current_balance,
        ]);
    }

    public function test_account_balance_is_not_deducted_when_transaction_is_approved_and_not_cleared(): void
    {
        $attributes = [
            'account_id'       => $this->account->uuid,
            'category_id'      => $this->category->uuid,
            'remarks'          => $this->faker->word,
            'inflow'           => $this->faker->numberBetween(1, 999999),
            'transaction_date' => $this->faker->date,
            'is_cleared'       => false,
            'is_approved'      => true,
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $this->account->current_balance,
        ]);
    }

    public function test_account_balance_is_not_deducted_when_transaction_is_cleared_but_not_approved(): void
    {
        $attributes = [
            'account_id'       => $this->account->uuid,
            'category_id'      => $this->category->uuid,
            'remarks'          => $this->faker->word,
            'inflow'           => $this->faker->numberBetween(1, 999999),
            'transaction_date' => $this->faker->date,
            'is_cleared'       => true,
            'is_approved'      => false,
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $this->account->current_balance,
        ]);
    }

    public function test_account_balance_is_correct_when_inflow_and_outflow_was_filled_at_the_same_time(): void
    {
        $attributes = [
            'account_id'       => $this->account->uuid,
            'category_id'      => $this->category->uuid,
            'remarks'          => $this->faker->word,
            'inflow'           => $this->faker->numberBetween(1, 999999),
            'outflow'          => $this->faker->numberBetween(1, 999999),
            'transaction_date' => $this->faker->date,
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $this->account->current_balance +
                $attributes['inflow'] -
                $attributes['outflow'],
        ]);
    }
}
