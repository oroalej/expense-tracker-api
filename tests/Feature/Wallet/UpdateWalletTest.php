<?php

namespace Tests\Feature\Wallet;

use App\Enums\WalletAccessTypeState;
use App\Enums\WalletTypeState;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class UpdateWalletTest extends TestCase
{
    public string $url;

    public User $user;

    public Wallet $wallet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->wallet = Wallet::factory()
            ->hasAttached($this->user, [
                'access_type' => WalletAccessTypeState::Owner,
            ])
            ->create();

        $this->url = "api/wallet/{$this->wallet->uuid}";
    }

    public function test_asserts_guest_now_allowed(): void
    {
        $this->putJson($this->url)->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_asserts_name_field_is_required(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url)
            ->assertJsonValidationErrors('name');
    }

    public function test_asserts_name_field_not_too_long(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url, ['description' => Str::random(192)])
            ->assertJsonValidationErrors('description');
    }

    public function test_asserts_description_is_optional(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url)
            ->assertJsonMissingValidationErrors('description');
    }

    public function test_asserts_description_field_not_too_long(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url, ['description' => Str::random(192)])
            ->assertJsonValidationErrors('description');
    }

    public function test_asserts_type_is_required(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url)
            ->assertJsonValidationErrors('wallet_type');
    }

    public function test_asserts_type_field_has_valid_enum(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url, ['wallet_type' => 9999999])
            ->assertJsonValidationErrors('wallet_type');
    }

    public function test_asserts_user_can_only_update_own_wallet(): void
    {
        $anotherUser = User::factory()->create();

        $this->actingAs($anotherUser)
            ->putJson($this->url)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_asserts_user_can_update_own_wallet(): void
    {
        $attributes = [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'current_balance' => $this->faker->numberBetween(),
            'wallet_type' => $this->faker->randomElement(
                WalletTypeState::getValues()
            ),
        ];

        $this->actingAs($this->user)
            ->putJson($this->url, $attributes)
            ->assertStatus(Response::HTTP_OK);
    }

    public function test_asserts_update_reflect_in_database(): void
    {
        $attributes = [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'current_balance' => $this->faker->numberBetween(),
            'wallet_type' => $this->faker->randomElement(
                WalletTypeState::getValues()
            ),
        ];

        $this->actingAs($this->user)
            ->putJson($this->url, $attributes)
            ->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('wallets', [
            'id' => $this->wallet->id,
            'name' => $attributes['name'],
            'description' => $attributes['description'],
            'current_balance' => $attributes['current_balance'],
        ]);
    }
}
