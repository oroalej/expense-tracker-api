<?php

namespace Tests\Feature\CategoryGroup;

use Illuminate\Support\Str;
use Tests\TestCase;

class StoreCategoryGroupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->url = "api/category-groups";
    }

    public function test_guest_not_allowed(): void
    {
        $this->postJson($this->url)->assertUnauthorized();
    }

    public function test_assert_name_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonValidationErrors('name');
    }

    public function test_assert_name_has_255_characters_max_length(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'name' => Str::random(255),
            ])
            ->assertJsonMissingValidationErrors('name');
    }

    public function test_assert_name_is_not_longer_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'name' => Str::random(256),
            ])
            ->assertJsonValidationErrors('name');
    }

    public function test_assert_notes_is_optional(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonMissingValidationErrors('notes');
    }

    public function test_assert_notes_has_255_characters_max_length(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'notes' => Str::random(255),
            ])
            ->assertJsonMissingValidationErrors('notes');
    }

    public function test_assert_notes_is_not_longer_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'notes' => Str::random(256),
            ])
            ->assertJsonValidationErrors('notes');
    }

    public function test_assert_user_can_create_category_group(): void
    {
        $attributes = [
            'name'  => $this->faker->word,
            'notes' => $this->faker->sentence,
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('category_groups', [
            'name'      => $attributes['name'],
            'notes'     => $attributes['notes'],
            'ledger_id' => $this->ledger->id
        ]);
    }

    public function test_assert_api_has_correct_structure(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'name'  => $this->faker->word,
                'notes' => $this->faker->sentence,
            ])
            ->assertJsonStructure(
                $this->apiStructure([
                    'id',
                    'name',
                    'notes',
                    'order',
                    'is_hidden'
                ])
            );
    }
}
