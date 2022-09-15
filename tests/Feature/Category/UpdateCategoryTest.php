<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\User;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Str;
use Tests\TestCase;

class UpdateCategoryTest extends TestCase
{
    public string $url;

    public Category $category;

    public CategoryGroup $categoryGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryGroup = CategoryGroup::factory()
            ->for($this->ledger)
            ->create();

        $this->category = Category::factory()
            ->for($this->categoryGroup)
            ->create();

        $this->url = "api/category-groups/{$this->categoryGroup->uuid}/categories/{$this->category->uuid}";
    }

    public function test_guest_not_allowed(): void
    {
        $this->putJson($this->url)->assertUnauthorized();
    }

    public function test_you_can_only_access_own_data(): void
    {
        $anotherUser = User::factory()->create();

        $this->actingAs($anotherUser)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url)
            ->assertNotFound();
    }

    public function test_assert_name_field_is_required(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url)
            ->assertJsonValidationErrors('name');
    }

    public function test_assert_name_has_255_characters_max_length(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, [
                'name' => Str::random(Builder::$defaultStringLength),
            ])
            ->assertJsonMissingValidationErrors('name');
    }

    public function test_assert_name_field_is_not_more_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, [
                'name' => Str::random(Builder::$defaultStringLength + 1),
            ])
            ->assertJsonValidationErrors('name');
    }

    public function test_assert_notes_has_255_characters_max_length(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, [
                'notes' => Str::random(Builder::$defaultStringLength),
            ])
            ->assertJsonMissingValidationErrors('notes');
    }

    public function test_assert_notes_is_not_more_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, [
                'notes' => Str::random(256),
            ])
            ->assertJsonValidationErrors('notes');
    }

    public function test_user_can_update_own_category(): void
    {
        $attributes = [
            'name' => $this->faker->word,
            'notes' => $this->faker->sentence,
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('categories', [
            'id' => $this->category->id,
            'name' => $attributes['name'],
            'notes' => $attributes['notes'],
            'category_group_id' => $this->categoryGroup->id,
        ]);
    }

    public function test_assert_user_can_update_category_group(): void
    {
        $attributes = [
            'name' => $this->faker->word,
            'notes' => $this->faker->sentence,
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('categories', [
            'id' => $this->category->id,
            'name' => $attributes['name'],
            'notes' => $attributes['notes'],
            'category_group_id' => $this->categoryGroup->id,
        ]);
    }

    public function test_assert_api_has_correct_structure(): void
    {
        $attributes = [
            'name' => $this->faker->word,
            'notes' => $this->faker->sentence,
        ];

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->putJson($this->url, $attributes)
            ->assertJsonStructure([
                'name',
                'notes',
                'created_at',
                'updated_at',
                'deleted_at',
            ]);
    }
}
