<?php

namespace Tests\Feature\Transaction;

use App\Enums\CategoryTypeState;
use App\Enums\WalletAccessTypeState;
use App\Models\Category;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class StoreTransactionTest extends TestCase
{
	public string $url = 'api/transaction';

	public User $user;

	protected function setUp(): void
	{
		parent::setUp();

		$this->user = User::factory()->create();
	}

	public function test_asserts_guest_are_not_allowed(): void
	{
		$this->postJson($this->url)->assertStatus(Response::HTTP_UNAUTHORIZED);
	}

	public function test_asserts_amount_field_is_required(): void
	{
		$this->actingAs($this->user)
			->postJson($this->url)
			->assertJsonValidationErrors('amount');
	}

	public function test_asserts_amount_field_only_accept_number(): void
	{
		$this->actingAs($this->user)
			->postJson($this->url, [
				'amount' => 'amount',
			])
			->assertJsonValidationErrors('amount');

		$this->actingAs($this->user)
			->postJson($this->url, [
				'amount' => '1111jj23',
			])
			->assertJsonValidationErrors('amount');
	}

	public function test_asserts_remarks_field_is_required(): void
	{
		$this->actingAs($this->user)
			->postJson($this->url)
			->assertJsonValidationErrors('remarks');
	}

	public function test_asserts_remarks_field_not_too_long(): void
	{
		$this->actingAs($this->user)
			->postJson($this->url, ['remarks' => Str::random(192)])
			->assertJsonValidationErrors('remarks');
	}

	public function test_asserts_transaction_date_is_required(): void
	{
		$this->actingAs($this->user)
			->postJson($this->url)
			->assertJsonValidationErrors('transaction_date');
	}

	public function test_asserts_transaction_date_only_accept_date(): void
	{
		$this->actingAs($this->user)
			->postJson($this->url, ['transaction_date' => 'transaction_date'])
			->assertJsonValidationErrors('transaction_date');
	}

	public function test_asserts_transaction_date_has_correct_format(): void
	{
		$this->actingAs($this->user)
			->postJson($this->url, ['transaction_date' => '01-31-2022'])
			->assertJsonValidationErrors('transaction_date');
	}

	public function test_asserts_category_is_required(): void
	{
		$this->actingAs($this->user)
			->postJson($this->url)
			->assertJsonValidationErrors('category_id');
	}

	public function test_asserts_only_own_category_can_be_used(): void
	{
		/** @var Category $category */
		$category = Category::factory()
			->for(User::factory()->create())
			->create();

		$this->actingAs($this->user)
			->postJson($this->url, ['category_id' => $category->id])
			->assertJsonValidationErrors('category_id');
	}

	public function test_asserts_wallet_is_required(): void
	{
		$this->actingAs($this->user)
			->postJson($this->url)
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
			->postJson($this->url, ['wallet_id' => $wallet->id])
			->assertJsonValidationErrors('wallet_id');
	}

	public function test_asserts_user_can_create_transaction(): void
	{
		/** @var Category $category */
		$category = Category::factory()
			->for($this->user)
			->create();

		/** @var Wallet $wallet */
		$wallet = Wallet::factory()
			->hasAttached($this->user, [
				'access_type' => WalletAccessTypeState::Owner->value,
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
			->postJson($this->url, $attributes)
			->assertCreated();
	}

	public function test_asserts_created_transaction_reflects_in_database(): void
	{
		/** @var Category $category */
		$category = Category::factory()
			->for($this->user)
			->create();

		/** @var Wallet $wallet */
		$wallet = Wallet::factory()
			->hasAttached($this->user, [
				'access_type' => WalletAccessTypeState::Owner->value,
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
			->postJson($this->url, $attributes)
			->assertCreated();

		$this->assertDatabaseCount('transactions', 1);
		$this->assertDatabaseHas('transactions', $attributes);
	}

	public function test_asserts_expense_transaction_deducts_wallet_balance(): void
	{
		/** @var Wallet $wallet */
		$wallet = Wallet::factory()
			->hasAttached($this->user, [
				'access_type' => WalletAccessTypeState::Owner,
			])
			->create();

		/** @var Category $category */
		$category = Category::factory()
			->for($this->user)
			->setCategoryType(CategoryTypeState::Expense)
			->create();

		$attributes = [
			'amount' => $this->faker->numberBetween(),
			'remarks' => $this->faker->sentence,
			'transaction_date' => $this->faker->date,
			'wallet_id' => $wallet->id,
			'category_id' => $category->id,
		];

		$this->actingAs($this->user)
			->postJson($this->url, $attributes)
			->assertCreated();

		$this->assertDatabaseHas('wallets', [
			'id' => $wallet->id,
			'current_balance' =>
				$wallet->current_balance - $attributes['amount'],
		]);
	}

	public function test_asserts_income_transaction_adds_wallet_balance(): void
	{
		/** @var Wallet $wallet */
		$wallet = Wallet::factory()
			->hasAttached($this->user, [
				'access_type' => WalletAccessTypeState::Owner,
			])
			->create();

		/** @var Category $category */
		$category = Category::factory()
			->for($this->user)
			->setCategoryType(CategoryTypeState::Income)
			->create();

		$attributes = [
			'amount' => $this->faker->numberBetween(),
			'remarks' => $this->faker->sentence,
			'transaction_date' => $this->faker->date,
			'wallet_id' => $wallet->id,
			'category_id' => $category->id,
		];

		$this->actingAs($this->user)
			->postJson($this->url, $attributes)
			->assertCreated();

		$this->assertDatabaseHas('wallets', [
			'id' => $wallet->id,
			'current_balance' =>
				$wallet->current_balance + $attributes['amount'],
		]);
	}
}
