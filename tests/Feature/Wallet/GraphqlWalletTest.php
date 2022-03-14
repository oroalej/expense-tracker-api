<?php

namespace Tests\Feature\Wallet;

use App\Enums\WalletAccessTypeState;
use App\Enums\WalletTypeState;
use App\Models\Scopes\UserAuthenticated;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\BaseGraphqlTest;

class GraphqlWalletTest extends BaseGraphqlTest
{
    public User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootRefreshesSchemaCache();

        $this->user = User::factory()->create();
        $this->wallet = Wallet::factory()
            ->hasAttached($this->user, [
                'access_type' => WalletAccessTypeState::Owner,
            ])
            ->create();
    }

    public function test_asserts_guest_not_allowed(): void
    {
        $walletId = $this->getWalletId();

        $this->graphQL(
            "{ wallet(id: $walletId) { {$this->getStringItemStructure()} } }"
        )
            ->assertOk()
            ->assertJson([
                'data' => ['wallet' => null],
            ]);

        $this->graphQL(
            "{ wallet(first: 5, page: 1) { data { {$this->getStringItemStructure()} } } }"
        )->assertOk();
    }

    public function test_asserts_user_gets_list_of_wallet(): void
    {
        $this->actingAs($this->user)
            ->graphQL(
                "{ wallets(first: 5, page: 1) { data { {$this->getStringItemStructure()} } paginatorInfo { {$this->getStringPaginationStructure()} } } }"
            )
            ->assertOk()
            ->assertJsonStructure($this->getResponseStructure('wallets'));
    }

    public function test_asserts_user_can_get_specific_wallet_data(): void
    {
        $walletId = $this->getWalletId();

        $this->actingAs($this->user)
            ->graphQL(
                "{ wallet(id: $walletId) { {$this->getStringItemStructure()}} }"
            )
            ->assertOk()
            ->assertJsonStructure($this->getResponseStructure('wallet'));
    }

    public function test_asserts_user_can_get_related_data(): void
    {
        $walletId = $this->getWalletId();

        $this->actingAs($this->user)
            ->graphQL("{ wallet(id: $walletId) {  users { id email } } }")
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'wallet' => [
                        'users' => [
                            '*' => ['id', 'email'],
                        ],
                    ],
                ],
            ]);
    }

    public function test_asserts_user_can_only_access_own_data(): void
    {
        $walletId = Wallet::factory()
            ->hasAttached(User::factory(), [
                'access_type' => WalletAccessTypeState::Owner,
            ])
            ->create()
            ->getAttribute('id');

        $this->actingAs($this->user)
            ->graphQL(
                "{ wallet(id: $walletId) { {$this->getStringItemStructure()} } }"
            )
            ->assertJson([
                'data' => [
                    'wallet' => null,
                ],
            ]);
    }

    public function test_asserts_query_returned_correct_filtered_data(): void
    {
        $limit = 2;
        $walletType = Wallet::withoutGlobalScope(UserAuthenticated::class)
            ->whereHas('users', function (Builder $builder) {
                $builder->where('users.id', $this->user->id);
            })
            ->inRandomOrder()
            ->first()
            ->getAttribute('wallet_type');

        $wallets = Wallet::withoutGlobalScope(UserAuthenticated::class)
            ->whereHas('users', function (Builder $builder) {
                $builder->where('users.id', $this->user->id);
            })
            ->select('wallet_type', 'uuid')
            ->where('wallet_type', $walletType)
            ->limit($limit)
            ->get()
            ->toArray();

        $walletTypeName = WalletTypeState::tryFrom($walletType)->name;

        $this->actingAs($this->user)
            ->graphQL(
                "{ wallets(first: $limit, page: 1, wallet_type: $walletTypeName) { data { uuid wallet_type } } }"
            )
            ->assertExactJson([
                'data' => [
                    'wallets' => ['data' => $wallets],
                ],
            ]);
    }

    public function getItemStructure(): array
    {
        return [
            'uuid',
            'name',
            'description',
            'current_balance',
            'wallet_type',

            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }

    private function getWalletId()
    {
        return Wallet::withoutGlobalScope(UserAuthenticated::class)
            ->whereHas('users', function (Builder $builder) {
                $builder->where('users.id', $this->user->id);
            })
            ->inRandomOrder()
            ->firstOrFail()
            ->getAttribute('id');
    }
}
