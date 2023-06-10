<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class UpdateCategoryTest extends TestCase
{
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
            ->for($this->ledger)
            ->create();

        $categoryId = Hashids::encode($this->category->id);

        $this->url = "api/categories/$categoryId";
    }

    public function test_guest_not_allowed(): void
    {
        $this->putJson($this->url)->assertUnauthorized();
    }

    public function test_you_can_only_access_own_data(): void
    {
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        $this->actingAs($anotherUser)
            ->appendHeaderLedgerId()
            ->putJson($this->url)
            ->assertNotFound();
    }

    public function test_assert_name_field_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url)
            ->assertJsonValidationErrors('name');
    }

    public function test_assert_name_has_255_characters_max_length(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'name' => Str::random(255),
            ])
            ->assertJsonMissingValidationErrors('name');
    }

    public function test_assert_name_field_is_not_more_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'name' => Str::random(256),
            ])
            ->assertJsonValidationErrors('name');
    }

    public function test_assert_notes_has_255_characters_max_length(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'notes' => Str::random(255),
            ])
            ->assertJsonMissingValidationErrors('notes');
    }

    public function test_assert_notes_is_not_more_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'notes' => Str::random(256),
            ])
            ->assertJsonValidationErrors('notes');
    }

    public function test_user_can_update_own_category(): void
    {
        $attributes = [
            'name'  => $this->faker->word,
            'notes' => $this->faker->sentence,
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('categories', [
            'id'                => $this->category->id,
            'category_group_id' => $this->categoryGroup->id,
            'name'              => $attributes['name'],
            'notes'             => $attributes['notes'],
        ]);
    }

    public function test_assert_user_can_update_category_group(): void
    {
        $attributes = [
            'name'  => $this->faker->word,
            'notes' => $this->faker->sentence,
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('categories', [
            'id'                => $this->category->id,
            'category_group_id' => $this->categoryGroup->id,
            'name'              => $attributes['name'],
            'notes'             => $attributes['notes'],
        ]);
    }

    public function test_assert_api_has_correct_structure(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'name'  => $this->faker->word,
                'notes' => $this->faker->sentence,
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
