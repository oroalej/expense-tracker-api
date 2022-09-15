<?php

namespace Tests\Feature\Transaction;

use App\Enums\AccountTypeState;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Category;
use App\Models\CategoryGroup;
use Illuminate\Database\Schema\Builder;
use Str;
use Tests\TestCase;

class StoreTransactionTest extends TestCase
{
    public string $url;

    public Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $cashAccountType = AccountType::find(AccountTypeState::Cash->value);

        $this->account = Account::factory()
            ->for($this->ledger)
            ->for($cashAccountType)
            ->create();

        $this->url = "api/accounts/{$this->account->uuid}/transactions";
    }

    public function test_guest_not_allowed(): void
    {
        $this->postJson($this->url)->assertUnauthorized();
    }

    public function test_account_is_required(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url)
            ->assertJsonValidationErrors('account_id');
    }

    public function test_account_is_valid(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, ['account_id' => 999999999])
            ->assertJsonValidationErrors('account_id');
    }

    public function test_category_is_required(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url)
            ->assertJsonValidationErrors('category_id');
    }

    public function test_category_is_valid(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, ['category_id' => 999999999])
            ->assertJsonValidationErrors('category_id');
    }

    public function test_remarks_is_optional(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url)
            ->assertJsonMissingValidationErrors('remarks');
    }

    public function test_remarks_is_not_more_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, [
                'remarks' => Str::random(Builder::$defaultStringLength + 1),
            ])
            ->assertJsonValidationErrors('remarks');
    }

    public function test_transaction_date_is_required(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url)
            ->assertJsonValidationErrors('transaction_date');
    }

    public function test_transaction_date_is_in_correct_format(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, ['transaction_date' => '12-31-2022'])
            ->assertJsonValidationErrors('transaction_date');
    }

    public function test_transaction_date_is_valid(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, ['transaction_date' => '2022-13-31'])
            ->assertJsonValidationErrors('transaction_date');
    }

    public function test_is_approved_is_optional(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url)
            ->assertJsonMissingValidationErrors('is_approved');
    }

    public function test_is_cleared_is_optional(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url)
            ->assertJsonMissingValidationErrors('is_cleared');
    }

    public function test_either_one_of_inflow_or_outflow_is_required(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, [
                'inflow' => null,
            ])
            ->assertJsonValidationErrors('outflow');

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, ['outflow' => 99999])
            ->assertJsonMissingValidationErrors('outflow')
            ->assertJsonMissingValidationErrors('inflow');

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, ['inflow' => 99999])
            ->assertJsonMissingValidationErrors('outflow')
            ->assertJsonMissingValidationErrors('inflow');
    }

    public function test_inflow_and_outflow_only_accept_number(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, ['outflow' => 'Hello Code Reviewer :)'])
            ->assertJsonValidationErrors('outflow');

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, ['inflow' => 'Hello Code Reviewer :)'])
            ->assertJsonValidationErrors('inflow');
    }

    public function test_inflow_and_outflow_accepts_decimals(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, [
                'outflow' => $this->faker->randomFloat(2, 1, 999999),
            ])
            ->assertJsonMissingValidationErrors('outflow');

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, [
                'inflow' => $this->faker->randomFloat(2, 1, 999999),
            ])
            ->assertJsonMissingValidationErrors('inflow');
    }

    public function test_inflow_and_outflow_only_accept_10_digits(): void
    {
        $elevenDigits = 99999999999;

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, [
                'outflow' => $elevenDigits,
            ])
            ->assertJsonValidationErrors('outflow');

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, [
                'inflow' => $elevenDigits,
            ])
            ->assertJsonValidationErrors('inflow');
    }

    public function test_user_can_create_transaction(): void
    {
        [$category] = $this->createNecessaryData();

        $attributes = [
            'account_id'       => $this->account->uuid,
            'category_id'      => $category->uuid,
            'remarks'          => $this->faker->word,
            'inflow'           => $this->faker->randomFloat(2, 1, 999999),
            'transaction_date' => $this->faker->date,
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseHas('transactions', [
            'account_id'  => $this->account->id,
            'category_id' => $category->id,
            'remarks'     => $attributes['remarks'],
            'inflow'      => $attributes['inflow'],
        ]);
    }

    public function test_api_has_correct_structure(): void
    {
        [$category] = $this->createNecessaryData();

        $attributes = [
            'account_id'       => $this->account->uuid,
            'category_id'      => $category->uuid,
            'remarks'          => $this->faker->word,
            'inflow'           => $this->faker->numberBetween(1, 9999999),
            'transaction_date' => $this->faker->date,
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, $attributes)
            ->assertCreated()
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

    protected function createNecessaryData(): array
    {
        $categoryGroup = CategoryGroup::factory()
            ->for($this->ledger)
            ->create();

        /** @var Category $category */
        $category = Category::factory()
            ->for($categoryGroup)
            ->create();

        return [$category, $categoryGroup];
    }
}
