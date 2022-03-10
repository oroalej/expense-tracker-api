<?php

namespace Tests\Feature\Transaction;

use App\Enums\CategoryTypeState;
use App\Enums\WalletAccessTypeState;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class UpdateTransactionTest extends TestCase
{
    public string $url;

    public User $user;

    public Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $category = Category::factory()
            ->for($this->user)
            ->setCategoryType(CategoryTypeState::Income)
            ->create();

        $wallet = Wallet::factory()
            ->hasAttached($this->user, [
                'access_type' => WalletAccessTypeState::Owner,
            ])
            ->create();

        $this->transaction = Transaction::factory()
            ->for($this->user)
            ->for($category)
            ->for($wallet)
            ->create();

        $this->url = "api/transaction/{$this->transaction->uuid}";
    }

    public function test_asserts_guest_are_not_allowed(): void
    {
        $this->putJson($this->url)->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_asserts_amount_field_is_required(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url)
            ->assertJsonValidationErrors('amount');
    }

    public function test_asserts_amount_field_only_accept_number(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url, [
                'amount' => 'amount',
            ])
            ->assertJsonValidationErrors('amount');

        $this->actingAs($this->user)
            ->putJson($this->url, [
                'amount' => '1111jj23',
            ])
            ->assertJsonValidationErrors('amount');
    }

    public function test_asserts_remarks_field_is_required(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url)
            ->assertJsonValidationErrors('remarks');
    }

    public function test_asserts_remarks_field_not_too_long(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url, ['remarks' => Str::random(192)])
            ->assertJsonValidationErrors('remarks');
    }

    public function test_asserts_transaction_date_is_required(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url)
            ->assertJsonValidationErrors('transaction_date');
    }

    public function test_asserts_transaction_date_only_accept_date(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url, ['transaction_date' => 'transaction_date'])
            ->assertJsonValidationErrors('transaction_date');
    }

    public function test_asserts_transaction_date_has_correct_format(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url, ['transaction_date' => '01-31-2022'])
            ->assertJsonValidationErrors('transaction_date');
    }

    public function test_asserts_category_is_required(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url)
            ->assertJsonValidationErrors('category_id');
    }

    public function test_asserts_only_own_category_can_be_used(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for(User::factory()->create())
            ->create();

        $this->actingAs($this->user)
            ->putJson($this->url, ['category_id' => $category->id])
            ->assertJsonValidationErrors('category_id');
    }

    public function test_asserts_wallet_is_required(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url)
            ->assertJsonValidationErrors('wallet_id');
    }

    public function test_asserts_only_own_wallet_can_be_used(): void
    {
        /** @var Wallet $wallet */
        $wallet = Wallet::factory()
            ->hasAttached(User::factory()->create(), [
                'access_type' => WalletAccessTypeState::Owner->value,
            ])
            ->create();

        $this->actingAs($this->user)
            ->putJson($this->url, ['wallet_id' => $wallet->id])
            ->assertJsonValidationErrors('wallet_id');
    }

    public function test_asserts_only_owner_of_the_transaction_can_update_the_data(): void
    {
        $this->actingAs(User::factory()->create())
            ->putJson($this->url)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_asserts_user_can_update_own_transaction(): void
    {
        $attributes = [
            'amount' => $this->faker->numberBetween(),
            'remarks' => $this->faker->sentence,
            'transaction_date' => $this->faker->date,
            'category_id' => $this->transaction->category_id,
            'wallet_id' => $this->transaction->wallet_id,
        ];

        $this->actingAs($this->user)
            ->putJson($this->url, $attributes)
            ->assertStatus(Response::HTTP_OK);
    }

    public function test_asserts_changes_reflected_in_database(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->user)
            ->setCategoryType(CategoryTypeState::Income)
            ->create();

        /** @var Wallet $wallet */
        $wallet = Wallet::factory()
            ->hasAttached($this->user, [
                'access_type' => WalletAccessTypeState::Owner,
            ])
            ->create();

        $attributes = [
            'amount' => $this->faker->numberBetween(),
            'remarks' => $this->faker->sentence,
            'transaction_date' => $this->faker->date,
            'category_id' => $category->id,
            'wallet_id' => $wallet->id,
        ];

        $this->actingAs($this->user)
            ->putJson($this->url, $attributes)
            ->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseHas('transactions', [
            'id' => $this->transaction->id,
            'amount' => $attributes['amount'],
            'remarks' => $attributes['remarks'],
            'category_id' => $attributes['category_id'],
            'wallet_id' => $attributes['wallet_id'],
        ]);
    }
}
