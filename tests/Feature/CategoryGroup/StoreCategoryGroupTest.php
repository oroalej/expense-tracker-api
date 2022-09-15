<?php

namespace Tests\Feature\CategoryGroup;

use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreCategoryGroupTest extends TestCase
{
    public string $url;

    protected function setUp(): void
    {
        parent::setUp();

        $this->url = 'api/category-groups';
    }

    public function test_guest_not_allowed(): void
    {
        $this->postJson($this->url)->assertUnauthorized();
    }

    public function test_assert_name_is_required(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url)
            ->assertJsonValidationErrors('name');
    }

    public function test_assert_name_has_255_characters_max_length(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, [
                'name' => Str::random(Builder::$defaultStringLength),
            ])
            ->assertJsonMissingValidationErrors('name');
    }

    public function test_assert_name_is_not_longer_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, [
                'name' => Str::random(Builder::$defaultStringLength + 1),
            ])
            ->assertJsonValidationErrors('name');
    }

    public function test_assert_notes_is_optional(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url)
            ->assertJsonMissingValidationErrors('notes');
    }

    public function test_assert_notes_has_255_characters_max_length(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, [
                'notes' => Str::random(Builder::$defaultStringLength),
            ])
            ->assertJsonMissingValidationErrors('notes');
    }

    public function test_assert_notes_is_not_longer_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, [
                'notes' => Str::random(Builder::$defaultStringLength + 1),
            ])
            ->assertJsonValidationErrors('notes');
    }

    public function test_assert_user_can_create_category_group(): void
    {
        $attributes = [
            'name' => $this->faker->word,
            'notes' => $this->faker->sentence,
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseCount('category_groups', 1);
        $this->assertDatabaseHas('category_groups', $attributes);
    }

    public function test_assert_api_has_correct_structure(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, [
                'name' => $this->faker->word,
                'notes' => $this->faker->sentence,
            ])
            ->assertJsonStructure([
                'uuid',
                'name',
                'notes',
                'created_at',
                'updated_at',
                'deleted_at',
            ]);
    }
}
