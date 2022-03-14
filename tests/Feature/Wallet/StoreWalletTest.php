<?php

namespace Tests\Feature\Wallet;

use App\Enums\WalletAccessTypeState;
use App\Enums\WalletTypeState;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreWalletTest extends TestCase
{
    public string $url = 'api/wallet';

    public User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_asserts_guest_not_allowed(): void
    {
        $this->postJson($this->url)->assertUnauthorized();
    }

    public function test_asserts_name_field_is_required(): void
    {
        $this->actingAs($this->user)
            ->postJson($this->url)
            ->assertJsonValidationErrors('name');
    }

    public function test_asserts_name_field_not_too_long(): void
    {
        $this->actingAs($this->user)
            ->postJson($this->url, ['description' => Str::random(192)])
            ->assertJsonValidationErrors('description');
    }

    public function test_asserts_description_is_optional(): void
    {
        $this->actingAs($this->user)
            ->postJson($this->url)
            ->assertJsonMissingValidationErrors('description');
    }

    public function test_asserts_description_field_not_too_long(): void
    {
        $this->actingAs($this->user)
            ->postJson($this->url, ['description' => Str::random(192)])
            ->assertJsonValidationErrors('description');
    }

    public function test_asserts_type_is_required(): void
    {
        $this->actingAs($this->user)
            ->postJson($this->url)
            ->assertJsonValidationErrors('wallet_type');
    }

    public function test_asserts_type_field_has_valid_enum(): void
    {
        $this->actingAs($this->user)
            ->postJson($this->url, ['wallet_type' => 9999999])
            ->assertJsonValidationErrors('wallet_type');
    }

    public function test_asserts_access_is_array(): void
    {
        $this->actingAs($this->user)
            ->postJson($this->url, ['access' => 999999999])
            ->assertJsonValidationErrors('access');
    }

    public function test_asserts_access_access_type_field_required(): void
    {
        $this->actingAs($this->user)
            ->postJson($this->url, [
                'access' => [['start_date' => '2022-01-31']],
            ])
            ->assertJsonValidationErrors('access.0.access_type');
    }

    public function test_asserts_access_access_type_field_has_valid_enum(): void
    {
        $this->actingAs($this->user)
            ->postJson($this->url, [
                'access' => [['access_type' => 99999999999]],
            ])
            ->assertJsonValidationErrors('access.0.access_type');
    }

    public function test_asserts_access_start_date_field_required(): void
    {
        $this->actingAs($this->user)
            ->postJson($this->url, ['access' => [['access_type' => 'owner']]])
            ->assertJsonValidationErrors('access.0.start_date');
    }

    public function test_asserts_access_start_date_field_has_correct_format(): void
    {
        $this->actingAs($this->user)
            ->postJson($this->url, [
                'access' => [['start_date' => '2022-01-31']],
            ])
            ->assertJsonMissingValidationErrors('access.0.start_date');
    }

    public function test_asserts_access_start_date_has_correct_format(): void
    {
        $this->actingAs($this->user)
            ->postJson($this->url, [
                'access' => [['start_date' => '01-31-2022']],
            ])
            ->assertJsonValidationErrors('access.0.start_date');
    }

    public function test_asserts_access_end_date_field_is_optional(): void
    {
        $this->actingAs($this->user)
            ->postJson($this->url, ['access' => [['access_type' => 'owner']]])
            ->assertJsonMissingValidationErrors('access.0.end_date');
    }

    public function test_asserts_access_end_date_field_has_correct_format(): void
    {
        $this->actingAs($this->user)
            ->postJson($this->url, ['access' => [['end_date' => '01-31-2022']]])
            ->assertJsonValidationErrors('access.0.end_date');
    }

    public function test_asserts_access_end_date_field_is_after_start_date(): void
    {
        $this->actingAs($this->user)
            ->postJson($this->url, [
                'access' => [
                    ['start_date' => '2022-01-31', 'end_date' => '2022-01-29'],
                ],
            ])
            ->assertJsonValidationErrors('access.0.end_date');
    }

    public function test_asserts_access_email_field_is_required(): void
    {
        $this->actingAs($this->user)
            ->postJson($this->url, ['access' => [['access_type' => 'owner']]])
            ->assertJsonValidationErrors('access.0.email');
    }

    public function test_asserts_access_email_field_only_accept_email(): void
    {
        $this->actingAs($this->user)
            ->postJson($this->url, ['access' => [['email' => Str::random()]]])
            ->assertJsonValidationErrors('access.0.email');
    }

    public function test_asserts_user_can_create_wallet(): void
    {
        $attributes = [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'current_balance' => $this->faker->numberBetween(0, 999999),
            'wallet_type' => $this->faker->randomElement(
                WalletTypeState::getValues()
            ),
        ];

        $this->actingAs($this->user)
            ->postJson($this->url, $attributes)
            ->assertCreated();
    }

    public function test_asserts_user_can_create_with_multiple_access(): void
    {
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();
        $attributes = [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'current_balance' => $this->faker->numberBetween(0, 999999),
            'wallet_type' => $this->faker->randomElement(
                WalletTypeState::getValues()
            ),
            'access' => [
                [
                    'access_type' => WalletAccessTypeState::view->value,
                    'start_date' => '2022-03-01',
                    'end_date' => '2022-03-05',
                    'email' => $anotherUser->email,
                ],
            ],
        ];

        $this->actingAs($this->user)
            ->postJson($this->url, $attributes)
            ->assertCreated();
    }

    public function test_asserts_data_is_saved_in_database(): void
    {
        $attributes = [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'current_balance' => $this->faker->numberBetween(0, 999999),
            'wallet_type' => $this->faker->randomElement(
                WalletTypeState::getValues()
            ),
        ];

        $this->actingAs($this->user)
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseCount('wallets', 1);
        $this->assertDatabaseHas('wallets', [
            'name' => $attributes['name'],
            'description' => $attributes['description'],
            'wallet_type' => $attributes['wallet_type'],
        ]);

        $this->assertDatabaseCount('user_wallet', 1);
        $this->assertDatabaseHas('user_wallet', [
            'access_type' => WalletAccessTypeState::Owner->value,
            'user_id' => $this->user->id,
        ]);
    }
}
