<?php

namespace Tests\Feature\Account;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\User;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Str;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class UpdateAccountTest extends TestCase
{
    public Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $accountType   = AccountType::first();
        $this->account = Account::factory()
            ->for($accountType)
            ->for($this->ledger)
            ->create();

        $accountId = Hashids::encode($this->account->id);

        $this->url = "api/accounts/$accountId";
    }

    public function test_asserts_guest_not_allowed(): void
    {
        $this->putJson($this->url)->assertUnauthorized();
    }

    public function test_user_can_only_access_own_data(): void
    {
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        $this->actingAs($anotherUser)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'name'            => $this->faker->word,
                'current_balance' => $this->faker->numberBetween(),
            ])
            ->assertNotFound();
    }

    public function test_assert_name_field_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url)
            ->assertJsonValidationErrors('name');
    }

    public function test_assert_name_has_255_characters_max_length(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'name' => Str::random(Builder::$defaultStringLength),
            ])
            ->assertJsonMissingValidationErrors('name');
    }

    public function test_assert_name_field_is_not_more_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'name' => Str::random(Builder::$defaultStringLength + 1),
            ])
            ->assertJsonValidationErrors('name');
    }

    public function test_account_type_cannot_be_change(): void
    {
        $accountTypeId = AccountType::where('id', '!=', $this->account->id)
            ->inRandomOrder()
            ->value('id');

        $attributes = [
            'name'            => $this->faker->word,
            'account_type_id' => Hashids::encode($accountTypeId),
            'current_balance' => $this->faker->numberBetween(1, 999999),
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes);

        $this->assertDatabaseHas('accounts', $this->account->only('id', 'account_type_id'));
    }

    public function test_assert_current_balance_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url)
            ->assertJsonValidationErrors('current_balance');
    }

    public function test_assert_current_balance_only_accept_number(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, ['current_balance' => 'HELLO WORLD'])
            ->assertJsonValidationErrors('current_balance');
    }

    public function test_assert_user_can_update_own_account(): void
    {
        $attributes = [
            'name'            => $this->faker->word,
            'current_balance' => $this->faker->numberBetween(1, 999999),
            'account_type_id' => Hashids::encode($this->account->account_type_id)
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseCount('accounts', 1);
        $this->assertDatabaseHas('accounts', [
            'name'            => $attributes['name'],
            'current_balance' => $attributes['current_balance'],
            'account_type_id' => $this->account->account_type_id,
        ]);
    }

    public function test_assert_correct_api_structure(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'name'            => $this->faker->word,
                'current_balance' => $this->faker->numberBetween(1, 999999),
            ])
            ->assertJsonStructure(
                $this->apiStructure([
                    'id',
                    'name',
                    'current_balance'
                ])
            );
    }
}
