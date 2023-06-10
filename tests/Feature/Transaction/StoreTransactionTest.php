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
use Vinkla\Hashids\Facades\Hashids;

class StoreTransactionTest extends TestCase
{
    public Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()
            ->for($this->ledger)
            ->for(AccountType::find(AccountTypeState::Cash->value))
            ->create();

        $this->url = "api/transactions";
    }

    public function test_guest_not_allowed(): void
    {
        $this->postJson($this->url)->assertUnauthorized();
    }

    public function test_account_id_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonValidationErrors('account_id');
    }

    public function test_account_id_is_valid(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, ['account_id' => 999999999])
            ->assertJsonValidationErrors('account_id');
    }

    public function test_category_id_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonValidationErrors('category_id');
    }

    public function test_category_id_is_valid(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, ['category_id' => 999999999])
            ->assertJsonValidationErrors('category_id');
    }

    public function test_remarks_is_optional(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonMissingValidationErrors('remarks');
    }

    public function test_remarks_is_not_more_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'remarks' => Str::random(Builder::$defaultStringLength + 1),
            ])
            ->assertJsonValidationErrors('remarks');
    }

    public function test_transaction_date_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonValidationErrors('transaction_date');
    }

    public function test_transaction_date_is_in_correct_format(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, ['transaction_date' => '12-31-2022'])
            ->assertJsonValidationErrors('transaction_date');
    }

    public function test_transaction_date_is_valid(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, ['transaction_date' => '2022-13-31'])
            ->assertJsonValidationErrors('transaction_date');
    }

    public function test_is_approved_is_optional(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonMissingValidationErrors('is_approved');
    }

    public function test_is_cleared_is_optional(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonMissingValidationErrors('is_cleared');
    }

    public function test_inflow_and_outflow_only_accept_number(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, ['outflow' => 'Hello Code Reviewer :)'])
            ->assertJsonValidationErrors('outflow');

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, ['inflow' => 'Hello Code Reviewer :)'])
            ->assertJsonValidationErrors('inflow');
    }

    public function test_an_error_occurred_when_only_outflow_is_provided_with_null_value()
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'outflow' => null,
            ])
            ->assertJsonValidationErrors('outflow');
    }

    public function test_an_error_occurred_when_only_inflow_is_provided_with_null_value()
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'inflow' => null,
            ])
            ->assertJsonValidationErrors('inflow');
    }

    public function test_an_error_occurred_when_both_are_null()
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'outflow' => null,
                'inflow'  => null,
            ])
            ->assertJsonValidationErrors('inflow');
    }

    public function test_no_errors_when_inflow_is_zero_and_outflow_is_populated()
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'outflow' => 1000,
                'inflow'  => 0,
            ])
            ->assertJsonMissingValidationErrors(['outflow', 'inflow']);
    }

    public function test_no_errors_when_outflow_is_zero_and_inflow_is_populated()
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'outflow' => 0,
                'inflow'  => 1000,
            ])
            ->assertJsonMissingValidationErrors(['outflow', 'inflow']);
    }

    public function test_error_occurred_when_both_inflow_and_outflow_populated(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'outflow' => 1000,
                'inflow'  => 1000,
            ])
            ->assertJsonValidationErrors(['outflow', 'inflow']);
    }

    public function test_no_errors_when_outflow_is_populated()
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, ['outflow' => 99999])
            ->assertJsonMissingValidationErrors(['outflow', 'inflow']);
    }

    public function test_no_errors_when_inflow_is_populated()
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, ['inflow' => 99999])
            ->assertJsonMissingValidationErrors(['outflow', 'inflow']);
    }

    public function test_inflow_and_outflow_accepts_decimals(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'outflow' => $this->faker->numberBetween(1, 999999),
            ])
            ->assertJsonMissingValidationErrors('outflow');

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'inflow' => $this->faker->numberBetween(1, 999999),
            ])
            ->assertJsonMissingValidationErrors('inflow');
    }

    public function test_inflow_and_outflow_only_accept_10_digits(): void
    {
        $elevenDigits = 99999999999;

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'outflow' => $elevenDigits,
            ])
            ->assertJsonValidationErrors('outflow');

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'inflow' => $elevenDigits,
            ])
            ->assertJsonValidationErrors('inflow');
    }

    public function test_user_can_create_transaction(): void
    {
        [$category] = $this->createNecessaryData();

        $attributes = [
            'account_id'       => Hashids::encode($this->account->id),
            'category_id'      => Hashids::encode($category->id),
            'remarks'          => $this->faker->word,
            'inflow'           => 5678,
            'transaction_date' => $this->faker->date,
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
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
            'account_id'       => Hashids::encode($this->account->id),
            'category_id'      => Hashids::encode($category->id),
            'remarks'          => $this->faker->word,
            'inflow'           => 1234,
            'transaction_date' => $this->faker->date,
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated()
            ->assertJsonStructure(
                $this->apiStructure([
                    'id',
                    'remarks',
                    'outflow',
                    'inflow',
                    'transaction_date',
                    'is_approved',
                    'is_cleared',
                    'created_at',
                    'updated_at',
                ])
            );
    }

    protected function createNecessaryData(): array
    {
        $categoryGroup = CategoryGroup::factory()
            ->for($this->ledger)
            ->create();

        /** @var Category $category */
        $category = Category::factory()
            ->for($categoryGroup)
            ->for($this->ledger)
            ->create();

        return [$category, $categoryGroup];
    }
}
