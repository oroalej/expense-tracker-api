<?php

namespace Tests\Feature\Ledger;

use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreLedgerTest extends TestCase
{
    public string $url;

    protected function setUp(): void
    {
        parent::setUp();

        $this->url = 'api/ledgers';
    }

    public function test_guest_not_allowed(): void
    {
        $this->postJson($this->url)->assertUnauthorized();
    }

    public function test_assert_name_is_required(): void
    {
        $this->actingAs($this->user)
            ->postJson($this->url)
            ->assertJsonValidationErrors('name');
    }

    public function test_assert_name_has_255_characters_max_length(): void
    {
        $this->actingAs($this->user)
            ->postJson($this->url, [
                'name' => Str::random(Builder::$defaultStringLength),
            ])
            ->assertJsonMissingValidationErrors('name');
    }

    public function test_assert_name_is_not_longer_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->postJson($this->url, [
                'name' => Str::random(Builder::$defaultStringLength + 1),
            ])
            ->assertJsonValidationErrors('name');
    }

    public function test_user_can_create_ledger(): void
    {
        $attributes = [
            'name' => $this->faker->word,
        ];

        $this->actingAs($this->user)
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('ledgers', [
            'name' => $attributes['name'],
        ]);
    }

    public function test_assert_api_has_correct_structure(): void
    {
        $this->actingAs($this->user)
            ->postJson($this->url, [
                'name' => $this->faker->word,
            ])
            ->assertJsonStructure([
                'uuid',
                'name',
                'created_at',
                'updated_at',
                'deleted_at',
            ]);
    }
}
