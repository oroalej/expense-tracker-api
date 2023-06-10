<?php

namespace Tests\Feature\User\SignUp;

use App\Listeners\Registered\InitialLedger;
use App\Models\User;
use Event;
use Illuminate\Auth\Events\Registered;
use Str;
use Tests\TestCase;

class EmailAndPasswordSignUpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->url = "api/register";
    }

    public function test_email_is_required(): void
    {
        $this->postJson($this->url)->assertJsonValidationErrors('email');
    }

    public function test_email_is_valid(): void
    {
        $this->postJson($this->url, [
            'email' => 'HelloWorld'
        ])->assertJsonValidationErrors('email');
    }

    public function test_email_is_unique(): void
    {
        $this->postJson($this->url, [
            'email' => $this->user->email
        ])->assertJsonValidationErrors('email');
    }

    public function test_password_is_required(): void
    {
        $this->postJson($this->url)
            ->assertJsonValidationErrors('password');
    }

    public function test_password_minimum_eight_characters(): void
    {
        $this->postJson($this->url, [
            'password' => 'HELLO'
        ])
            ->assertJsonValidationErrors('password');
    }

    public function test_password_must_be_mixed_case(): void
    {
        $this->postJson($this->url, [
            'password' => strtolower(Str::random(10))
        ])
            ->assertJsonValidationErrors('password');
    }

    public function test_guest_was_able_to_create_a_user(): void
    {
        $this->postJson($this->url, [
            'email'    => $this->faker->email,
            'password' => 'UqHLCjvCqzziUU'
        ])
            ->assertCreated();
    }

    public function test_api_has_correct_structure(): void
    {
        $this->postJson($this->url, [
            'email'    => $this->faker->email,
            'password' => 'UqHLCjvCqzziUU'
        ])
            ->assertCreated()
            ->assertJsonStructure(
                $this->apiStructure([
                    'user'    => [
                        'id',
                        'name',
                        'email',
                    ],
                    'ledgers' => [
                       '*' => [
                           'id',
                           'name',
                           'is_archived',
                           'date_format',
                           'number_format' => [
                               'name',
                               'abbr',
                               'code',
                               'locale'
                           ],
                           'created_at',
                           'updated_at',
                           'archived_at',
                           'deleted_at',
                       ]
                    ],
                    'token'
                ])
            );
    }

    public function test_registered_events_triggered(): void
    {
        Event::fake([
            Registered::class
        ]);

        /** @var User $user */
        User::factory()->create();


        Event::assertListening(Registered::class, InitialLedger::class);
    }

//    public function test_sent_notification_upon_registration(): void
//    {
//        Mail::fake();
//
//        /** @var User $user */
//        User::factory()->create();
//
//        Mail::assertSent(Registered::class);
//    }
}
