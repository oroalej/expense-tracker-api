<?php

namespace Category;

use App\Models\CategoryGroup;
use Illuminate\Support\Str;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class StoreCategoryTest extends TestCase
{
    public CategoryGroup $categoryGroup;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var CategoryGroup $categoryGroup */
        $this->categoryGroup = CategoryGroup::factory()
            ->for($this->ledger)
            ->create();

        $categoryGroupId = Hashids::encode($this->categoryGroup->id);

        $this->url = "api/category-groups/$categoryGroupId/categories";
    }

    public function test_asserts_guest_not_allowed(): void
    {
        $this->postJson($this->url)->assertUnauthorized();
    }

    public function test_assert_name_field_is_required(): void
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

    public function test_asserts_name_field_is_not_more_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'name' => Str::random(256),
            ])
            ->assertJsonValidationErrors('name');
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

    public function test_asserts_notes_is_not_more_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'notes' => Str::random(256),
            ])
            ->assertJsonValidationErrors('notes');
    }

    public function test_asserts_user_can_create_category(): void
    {
        $attributes = [
            'name'  => $this->faker->word,
            'notes' => $this->faker->sentence
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('categories', [
            'name'              => $attributes['name'],
            'category_group_id' => $this->categoryGroup->id,
        ]);
    }

    public function test_created_category_is_not_hidden(): void
    {
        $attributes = [
            'name'  => $this->faker->word,
            'notes' => $this->faker->sentence
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('categories', [
            'name'              => $attributes['name'],
            'is_hidden'         => false,
            'category_group_id' => $this->categoryGroup->id,
        ]);
    }

    public function test_assert_api_has_correct_structure(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'name'              => $this->faker->word,
                'notes'             => $this->faker->sentence,
                'category_group_id' => $this->categoryGroup->uuid,
            ])
            ->assertJsonStructure(
                $this->apiStructure([
                    'id',
                    'name',
                    'notes',
                    'category_group_id'
                ])
            );
    }
}
