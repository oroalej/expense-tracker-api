<?php

namespace Tests\Feature\Transaction;

use App\Enums\CategoryTypeState;
use App\Enums\WalletAccessTypeState;
use App\Models\Category;
use App\Models\Scopes\UserAuthenticated;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\BaseGraphqlTest;

class GraphqlTransactionTest extends BaseGraphqlTest
{
    public User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootRefreshesSchemaCache();

        $this->user = User::factory()->create();

        $this->generateTransactions($this->user);
    }

    public function test_asserts_user_gets_list_of_transactions(): void
    {
        $this->actingAs($this->user)
            ->graphQL(
                "{ transactions(first: 5, page: 1) { data { {$this->getStringItemStructure()} } paginatorInfo { {$this->getStringPaginationStructure()} } } }"
            )
            ->assertOk()
            ->assertJsonStructure($this->getResponseStructure('transactions'));
    }

    public function test_asserts_user_can_get_specific_wallet_data(): void
    {
        $transactionUuid = $this->getTransactionUuid();

        $this->actingAs($this->user)
            ->graphQL(
                "{ transaction(id: \"$transactionUuid\") { {$this->getStringItemStructure()}} }"
            )
            ->assertOk()
            ->assertJsonStructure($this->getResponseStructure('transaction'));
    }

    public function test_asserts_user_can_get_related_data(): void
    {
        $transactionUuid = $this->getTransactionUuid();

        $this->actingAs($this->user)
            ->graphQL(
                "{ transaction(id: \"$transactionUuid\") { user { id email } } }"
            )
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'transaction' => [
                        'user' => ['id', 'email'],
                    ],
                ],
            ]);
    }

    public function test_asserts_user_can_only_access_own_data(): void
    {
        $transactions = $this->generateTransactions(
            User::factory()->create(),
            1
        );

        $transactionId = $transactions->first()->getAttribute('uuid');

        $this->actingAs($this->user)
            ->graphQL(
                "{ transaction(id: \"$transactionId\") { {$this->getStringItemStructure()} } }"
            )
            ->assertJson([
                'data' => [
                    'transaction' => null,
                ],
            ]);
    }

    public function test_asserts_query_returned_correct_filtered_data(): void
    {
        $walletId = Transaction::withoutGlobalScope(UserAuthenticated::class)
            ->where('user_id', $this->user->id)
            ->inRandomOrder()
            ->first()
            ->getAttribute('wallet_id');

        $transactions = Transaction::withoutGlobalScope(
            UserAuthenticated::class
        )
            ->select('uuid', 'wallet_id')
            ->where('user_id', $this->user->id)
            ->where('wallet_id', $walletId)
            ->limit(2)
            ->get()
            ->toArray();

        $this->actingAs($this->user)
            ->graphQL(
                "{ transactions(first: 2, page: 1, wallet_id: $walletId) { data { uuid wallet_id } } }"
            )
            ->assertExactJson([
                'data' => [
                    'transactions' => ['data' => $transactions],
                ],
            ]);
    }

    public function getTransactionUuid()
    {
        return Transaction::withoutGlobalScope(UserAuthenticated::class)
            ->where('user_id', $this->user->id)
            ->inRandomOrder()
            ->first()
            ->getAttribute('uuid');
    }

    public function generateTransactions(user $user, int $count = 5)
    {
        $categoryIds = Category::factory()
            ->for($user)
            ->setCategoryType(CategoryTypeState::Income)
            ->count(2)
            ->create()
            ->pluck('id');

        $walletIds = Wallet::factory()
            ->hasAttached($user, [
                'access_type' => WalletAccessTypeState::Owner,
            ])
            ->count(2)
            ->create()
            ->pluck('id');

        return Transaction::factory()
            ->for($user)
            ->state(new Sequence(['wallet_id' => $walletIds->random()]))
            ->state(
                new Sequence(
                    fn($sequence) => ['category_id' => $categoryIds->random()]
                )
            )
            ->state(
                new Sequence(
                    fn($sequence) => ['wallet_id' => $walletIds->random()]
                )
            )
            ->count($count)
            ->create();
    }

    public function getItemStructure(): array
    {
        return [
            'uuid',
            'amount',
            'remarks',
            'transaction_date',

            'user_id',
            'wallet_id',
            'category_id',

            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }
}
