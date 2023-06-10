<?php

namespace Tests\Feature\User\Login;

use Tests\TestCase;

class EmailAndPasswordLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->url = "api/sanctum/token";
    }

    public function test_email_is_required(): void
    {
        $this->postJson($this->url)
            ->assertJsonValidationErrors('email');
    }

    public function test_email_is_valid(): void
    {
        $this->postJson($this->url, [
            'email' => 'HelloWorld'
        ])->assertJsonValidationErrors('email');
    }

    public function test_password_is_required(): void
    {
        $this->postJson($this->url)
            ->assertJsonValidationErrors('password');
    }

    public function test_user_can_logged_in(): void
    {
        $this->postJson($this->url, [
            'email'    => $this->user->email,
            'password' => 'password'
        ])->assertOk();
    }

    public function test_api_has_correct_structure()
    {
        $this->postJson($this->url, [
            'email'    => $this->user->email,
            'password' => 'password'
        ])
            ->assertOk()
            ->assertJsonStructure(
                $this->apiStructure([
                    'user' => [
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
}
