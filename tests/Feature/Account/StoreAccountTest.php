<?php

namespace Tests\Feature\Account;

use App\Models\AccountType;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreAccountTest extends TestCase
{
    public string $url;

    protected function setUp(): void
    {
        parent::setUp();

        $this->url = 'api/accounts';
    }

    public function test_asserts_guest_not_allowed(): void
    {
        $this->postJson($this->url)->assertUnauthorized();
    }

    public function test_assert_name_field_is_required(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url)
            ->assertJsonValidationErrors('name');
    }

    public function test_assert_name_has_255_characters_max_length(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, [
                'name' => Str::random(Builder::$defaultStringLength),
            ])
            ->assertJsonMissingValidationErrors('name');
    }

    public function test_assert_name_field_is_not_more_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, [
                'name' => Str::random(Builder::$defaultStringLength + 1),
            ])
            ->assertJsonValidationErrors('name');
    }

    public function test_assert_current_balance_is_required(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url)
            ->assertJsonValidationErrors('current_balance');
    }

    public function test_assert_current_balance_only_accept_number(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, ['current_balance' => 'HELLO WORLD'])
            ->assertJsonValidationErrors('current_balance');
    }

    public function test_assert_account_type_is_required(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url)
            ->assertJsonValidationErrors('account_type_id');
    }

    public function test_assert_account_type_is_valid(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, ['account_type_id' => 999999])
            ->assertJsonValidationErrors('account_type_id');
    }

    public function test_assert_user_can_create_account(): void
    {
        $account = AccountType::first();

        $attributes = [
            'name'            => $this->faker->word,
            'account_type_id' => $account->uuid,
            'current_balance' => $this->faker->numberBetween(1, 999999),
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseCount('accounts', 1);
        $this->assertDatabaseHas('accounts', [
            'name'            => $attributes['name'],
            'ledger_id'       => $this->ledger->id,
            'account_type_id' => $account->id
        ]);
    }

    public function created_account_is_not_archived(): void
    {
        $account = AccountType::first();

        $attributes = [
            'name'            => $this->faker->word,
            'account_type_id' => $account->uuid,
            'current_balance' => $this->faker->numberBetween(1, 999999),
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('accounts', [
            'is_archived' => false
        ]);
    }

    public function test_assert_correct_api_structure(): void
    {
        $account = AccountType::first();

        $attributes = [
            'name'            => $this->faker->word,
            'account_type_id' => $account->uuid,
            'current_balance' => $this->faker->numberBetween(1, 999999),
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, $attributes)
            ->assertCreated()
            ->assertJsonStructure([
                'uuid',
                'name',
                'current_balance',
                'created_at',
                'updated_at',
                'deleted_at',
            ]);
    }
}
