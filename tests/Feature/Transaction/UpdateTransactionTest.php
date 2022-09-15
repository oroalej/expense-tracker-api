<?php

namespace Tests\Feature\Transaction;

use App\Enums\AccountTypeState;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Str;
use Tests\TestCase;

class UpdateTransactionTest extends TestCase
{
    public string $url;

    public Transaction $transaction;

    public Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $cashAccountType = AccountType::find(AccountTypeState::Cash->value);

        /** @var Account $account */
        $this->account = Account::factory()
            ->for($this->ledger)
            ->for($cashAccountType)
            ->create();

        $this->transaction = Transaction::factory()
            ->for(
                Category::factory()->for(
                    CategoryGroup::factory()->for($this->ledger)
                )
            )
            ->for($this->account)
            ->setOutflow()
            ->create();

        $this->url = "api/accounts/{$this->account->uuid}/transactions/{$this->transaction->uuid}";
    }

    public function test_guest_are_not_allowed(): void
    {
        $this->putJson($this->url)->assertUnauthorized();
    }

    public function test_user_can_only_access_own_data(): void
    {
        $anotherUser = User::factory()->create();

        $this->actingAs($anotherUser)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url)
            ->assertNotFound();
    }

    public function test_account_is_required(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url)
            ->assertJsonValidationErrors('account_id');
    }

    public function test_account_is_valid(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, ['account_id' => 999999999])
            ->assertJsonValidationErrors('account_id');
    }

    public function test_category_is_required(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url)
            ->assertJsonValidationErrors('category_id');
    }

    public function test_category_is_valid(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, ['category_id' => 999999999])
            ->assertJsonValidationErrors('category_id');
    }

    public function test_remarks_is_optional(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url)
            ->assertJsonMissingValidationErrors('remarks');
    }

    public function test_remarks_is_not_more_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, [
                'remarks' => Str::random(Builder::$defaultStringLength + 1),
            ])
            ->assertJsonValidationErrors('remarks');
    }

    public function test_transaction_date_is_required(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url)
            ->assertJsonValidationErrors('transaction_date');
    }

    public function test_transaction_date_is_in_correct_format(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, ['transaction_date' => '12-31-2022'])
            ->assertJsonValidationErrors('transaction_date');
    }

    public function test_transaction_date_is_valid(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, ['transaction_date' => '2022-13-31'])
            ->assertJsonValidationErrors('transaction_date');
    }

    public function test_is_approved_is_optional(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url)
            ->assertJsonMissingValidationErrors('is_approved');
    }

    public function test_is_cleared_is_optional(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url)
            ->assertJsonMissingValidationErrors('is_cleared');
    }

    public function test_either_one_of_inflow_or_outflow_is_required(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, [
                'inflow' => null,
            ])
            ->assertJsonValidationErrors('outflow');

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, ['outflow' => 99999])
            ->assertJsonMissingValidationErrors('outflow')
            ->assertJsonMissingValidationErrors('inflow');

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, ['inflow' => 99999])
            ->assertJsonMissingValidationErrors('outflow')
            ->assertJsonMissingValidationErrors('inflow');
    }

    public function test_inflow_and_outflow_only_accept_number(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, ['outflow' => 'Hello Code Reviewer :)'])
            ->assertJsonValidationErrors('outflow');

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, ['inflow' => 'Hello Code Reviewer :)'])
            ->assertJsonValidationErrors('inflow');
    }

    public function test_inflow_and_outflow_accepts_decimals(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, [
                'outflow' => $this->faker->randomFloat(2, 1, 999999),
            ])
            ->assertJsonMissingValidationErrors('outflow');

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, [
                'inflow' => $this->faker->randomFloat(2, 1, 999999),
            ])
            ->assertJsonMissingValidationErrors('inflow');
    }

    public function test_inflow_and_outflow_only_accept_10_digits(): void
    {
        $elevenDigits = 99999999999;

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, [
                'outflow' => $elevenDigits,
            ])
            ->assertJsonValidationErrors('outflow');

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, [
                'inflow' => $elevenDigits,
            ])
            ->assertJsonValidationErrors('inflow');
    }

    public function test_user_can_update_own_transaction(): void
    {
        $attributes = [
            'inflow'           => $this->transaction->inflow,
            'outflow'          => $this->faker->randomFloat(2, 1, 999999),
            'remarks'          => $this->faker->sentence,
            'transaction_date' => $this->faker->date,
            'category_id'      => $this->transaction->category->uuid,
            'account_id'       => $this->account->uuid,
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('transactions', [
            'id'               => $this->transaction->id,
            'outflow'          => $attributes['outflow'],
            'remarks'          => $attributes['remarks'],
            'transaction_date' => $attributes['transaction_date'],
        ]);
    }

    public function test_api_structure_is_correct(): void
    {
        $attributes = [
            'inflow'           => $this->transaction->inflow,
            'outflow'          => $this->faker->randomFloat(2, 1, 999999),
            'remarks'          => $this->faker->sentence,
            'transaction_date' => $this->faker->date,
            'category_id'      => $this->transaction->category->uuid,
            'account_id'       => $this->account->uuid,
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, $attributes)
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'remarks',
                'outflow',
                'inflow',
                'transaction_date',
                'is_approved',
                'is_cleared',
                'created_at',
                'updated_at',
            ]);
    }
}
