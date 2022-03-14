<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoginTest extends TestCase
{
    protected string $url = 'api/sanctum/token';

    public function test_email_is_required(): void
    {
        $this->postJson($this->url)->assertJsonValidationErrors('email');
    }

    public function test_email_has_correct_format(): void
    {
        $this->postJson($this->url, [
            'email' => Str::random(),
        ])->assertJsonValidationErrors('email');
    }

    public function test_password_is_required(): void
    {
        $this->postJson($this->url)->assertJsonValidationErrors('password');
    }

    public function test_return_error_using_incorrect_information(): void
    {
        $this->postJson($this->url, [
            'email' => $this->faker->email,
            'password' => Str::random(),
        ])->assertJsonValidationErrors('email');
    }

    public function test_user_received_token_using_correct_credentials(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->postJson($this->url, [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk();
    }
}
