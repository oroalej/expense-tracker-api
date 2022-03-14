<?php

namespace Tests\Feature\User;

use App\Enums\WalletTypeState;
use App\Listeners\Registered\CreateCashWallet;
use App\Listeners\Registered\SeedCategories;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\TestCase;
use Throwable;

class RegisterTest extends TestCase
{
    protected string $url = 'api/register';

    public function test_asserts_name_is_required(): void
    {
        $this->postJson($this->url)->assertJsonValidationErrors('name');
    }

    public function test_asserts_name_is_not_too_long(): void
    {
        $this->postJson($this->url, [
            'name' => Str::random(256),
        ])->assertJsonValidationErrors('name');
    }

    public function test_asserts_email_is_required(): void
    {
        $this->postJson($this->url)->assertJsonValidationErrors('email');
    }

    public function test_asserts_email_has_valid_input(): void
    {
        $this->postJson($this->url, [
            'email' => Str::random(),
        ])->assertJsonValidationErrors('email');
    }

    public function test_asserts_email_is_unique(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->postJson($this->url, [
            'email' => $user->email,
        ])->assertJsonValidationErrors('email');
    }

    public function test_asserts_email_is_not_too_long(): void
    {
        $randomEmail = Str::random(255) . '.gmail.com';

        $this->postJson($this->url, [
            'email' => $randomEmail,
        ])->assertJsonValidationErrors('email');
    }

    public function test_a_user_can_register(): void
    {
        Event::fake();

        $attributes = [
            'name' => 'Alexander Jeam Oro',
            'email' => 'alexanderjeamoro@gmail.com',
            'password' => 'hello123',
            'password_confirmation' => 'hello123',
        ];

        $this->postJson('api/register', $attributes)->assertCreated();
        $this->assertDatabaseHas('users', [
            'email' => $attributes['email'],
            'name' => $attributes['name'],
        ]);

        Event::assertDispatched(Registered::class);
    }

    public function test_user_event_seed_categories_triggered(): void
    {
        $user = User::factory()->create();

        $registeredEvent = new Registered($user);

        $seedCategoriesListener = new SeedCategories();
        $seedCategoriesListener->handle($registeredEvent);

        $this->assertDatabaseCount('categories', 35);
    }

    /**
     * @throws Throwable
     */
    public function test_user_event_create_wallet_triggered(): void
    {
        $this->withoutExceptionHandling();

        $registeredEvent = new Registered(User::factory()->create());

        $seedCategoriesListener = new CreateCashWallet();
        $seedCategoriesListener->handle($registeredEvent);

        $this->assertDatabaseCount('wallets', 1);
        $this->assertDatabaseHas('wallets', [
            'wallet_type' => WalletTypeState::Cash->value,
        ]);
    }
}
