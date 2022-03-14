<?php

namespace Category;

use App\Enums\CategoryTypeState;
use App\Enums\WalletTypeState;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Str;
use Tests\TestCase;

class UpdateCategoryTest extends TestCase
{
    public Category $category;

    public User $user;

    public string $url;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->category = Category::factory()
            ->for($this->user)
            ->setCategoryType(CategoryTypeState::Income)
            ->create();

        $this->url = "api/category/{$this->category->id}";
    }

    public function test_asserts_guest_not_allowed(): void
    {
        $this->putJson($this->url)->assertUnauthorized();
    }

    public function test_asserts_name_field_is_required(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url)
            ->assertJsonValidationErrors('name');
    }

    public function test_asserts_name_field_is_not_too_long(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url, ['name' => Str::random(192)])
            ->assertJsonValidationErrors('name');
    }

    public function test_asserts_description_is_not_too_long(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url, ['description' => Str::random(192)])
            ->assertJsonValidationErrors('description');
    }

    public function test_asserts_category_type_field_is_required(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url)
            ->assertJsonValidationErrors('category_type');
    }

    public function test_asserts_only_valid_value_allowed_in_category_type(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url, [
                'category_type' => 9999999,
            ])
            ->assertJsonValidationErrors('category_type');
    }

    public function test_asserts_parent_field_is_optional(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url)
            ->assertJsonMissingValidationErrors('parent_id');
    }

    public function test_asserts_parent_id_is_valid(): void
    {
        $this->actingAs($this->user)
            ->putJson($this->url, ['parent_id' => 9999999])
            ->assertJsonValidationErrors('parent_id');
    }

    public function test_asserts_only_own_category_can_be_attached_to_parent_field(): void
    {
        /** @var Category $anotherUserCategory */
        $anotherUserCategory = Category::factory()
            ->setCategoryType(CategoryTypeState::Debt)
            ->for(User::factory()->create())
            ->create();

        $this->actingAs($this->user)
            ->putJson($this->url, [
                'category_type' => CategoryTypeState::Expense,
                'parent_id' => $anotherUserCategory->id,
            ])
            ->assertJsonValidationErrors('parent_id');
    }

    public function test_asserts_parent_and_child_category_type_are_the_same(): void
    {
        /** @var Category $anotherCategory */
        $anotherCategory = Category::factory()
            ->for($this->user)
            ->setCategoryType(CategoryTypeState::Debt)
            ->create();

        $this->actingAs($this->user)
            ->putJson($this->url, [
                'category_type' => CategoryTypeState::Income->value,
                'parent_id' => $anotherCategory->id,
            ])
            ->assertJsonValidationErrors('parent_id');
    }

    public function test_asserts_can_only_update_own_category(): void
    {
        $anotherUser = User::factory()->create();

        $attributes = [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'category_type' => CategoryTypeState::Debt->value,
        ];

        $this->actingAs($anotherUser)
            ->putJson($this->url, $attributes)
            ->assertNotFound();

        $this->assertDatabaseHas('categories', [
            'id' => $this->category->id,
            'name' => $this->category->name,
            'description' => $this->category->description,
            'category_type' => $this->category->category_type,
        ]);
    }

    public function test_asserts_changing_of_category_type_is_not_allowed_once_category_was_used_in_transaction(): void
    {
        $wallet = Wallet::factory()
            ->setWalletType(WalletTypeState::Cash)
            ->create();

        Transaction::factory()
            ->for($this->user)
            ->for($wallet)
            ->for($this->category)
            ->create();

        $attributes = [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'category_type' => CategoryTypeState::Debt->value,
        ];

        $this->actingAs($this->user)
            ->putJson($this->url, $attributes)
            ->assertJsonValidationErrors('category_type');

        $this->assertDatabaseHas('categories', [
            'id' => $this->category->id,
            'category_type' => $this->category->category_type,
        ]);
    }

    public function test_asserts_user_can_update_own_category(): void
    {
        $attributes = [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'category_type' => CategoryTypeState::Debt->value,
        ];

        $this->actingAs($this->user)
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseMissing('transactions', [
            'category_id' => $this->category->id,
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $this->category->id,
            'name' => $attributes['name'],
            'description' => $attributes['description'],
            'category_type' => $attributes['category_type'],
        ]);
    }

    public function test_asserts_user_can_update_parent_category(): void
    {
        /** @var Category $anotherCategory */
        $anotherCategory = Category::factory()
            ->for($this->user)
            ->setCategoryType(CategoryTypeState::Debt)
            ->create();

        $attributes = [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'category_type' => CategoryTypeState::Debt->value,
            'parent_id' => $anotherCategory->id,
        ];

        $this->actingAs($this->user)
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('categories', [
            'id' => $this->category->id,
            'name' => $attributes['name'],
            'description' => $attributes['description'],
            'category_type' => $attributes['category_type'],
            'parent_id' => $anotherCategory->id,
        ]);
    }
}
