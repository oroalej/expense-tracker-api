<?php

namespace Category;

use App\Models\CategoryGroup;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreCategoryTest extends TestCase
{
    public string $url;

    public CategoryGroup $categoryGroup;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var CategoryGroup $categoryGroup */
        $this->categoryGroup = CategoryGroup::factory()
            ->for($this->ledger)
            ->create();

        $this->url = "api/category-groups/{$this->categoryGroup->uuid}/categories";
    }

    public function test_asserts_guest_not_allowed(): void
    {
        $this->postJson($this->url)->assertUnauthorized();
    }

    public function test_assert_name_field_is_required(): void
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

    public function test_asserts_name_field_is_not_more_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, [
                'name' => Str::random(Builder::$defaultStringLength + 1),
            ])
            ->assertJsonValidationErrors('name');
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

    public function test_asserts_notes_is_not_more_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, [
                'notes' => Str::random(Builder::$defaultStringLength + 1),
            ])
            ->assertJsonValidationErrors('notes');
    }

    public function test_asserts_user_can_create_category(): void
    {
        $attributes = [
            'name'              => $this->faker->word,
            'notes'             => $this->faker->sentence,
            'category_group_id' => $this->categoryGroup->uuid
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseCount('categories', 1);
        $this->assertDatabaseHas('categories', [
            'name'              => $attributes['name'],
            'category_group_id' => $this->categoryGroup->id
        ]);
    }

    public function test_create_category_is_not_hidden(): void
    {
        $attributes = [
            'name'              => $this->faker->word,
            'notes'             => $this->faker->sentence,
            'category_group_id' => $this->categoryGroup->uuid
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseCount('categories', 1);
        $this->assertDatabaseHas('categories', [
            'name'              => $attributes['name'],
            'is_hidden'         => false,
            'category_group_id' => $this->categoryGroup->id
        ]);
    }

    public function test_assert_api_has_correct_structure(): void
    {
        $attributes = [
            'name'              => $this->faker->word,
            'notes'             => $this->faker->sentence,
            'category_group_id' => $this->categoryGroup->uuid,
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, $attributes)
            ->assertJsonStructure([
                'name',
                'notes',
                'created_at',
                'updated_at',
                'deleted_at',
            ]);
    }
}
