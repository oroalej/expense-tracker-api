<?php

namespace Category;

use App\Enums\CategoryTypeState;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class StoreCategoryTest extends TestCase
{
	public string $url = 'api/category';

	public User $user;

	protected function setUp(): void
	{
		parent::setUp();

		$this->user = User::factory()->create();
	}

	public function test_asserts_guest_not_allowed(): void
	{
		$this->postJson($this->url)->assertStatus(Response::HTTP_UNAUTHORIZED);
	}

	public function test_assert_name_field_is_required(): void
	{
		$this->actingAs($this->user)
			->postJson($this->url)
			->assertJsonValidationErrors('name');
	}

	public function test_asserts_name_field_is_not_too_long(): void
	{
		$this->actingAs($this->user)
			->postJson($this->url, ['name' => Str::random(192)])
			->assertJsonValidationErrors('name');
	}

	public function test_asserts_description_is_not_too_long(): void
	{
		$this->actingAs($this->user)
			->postJson($this->url, ['description' => Str::random(192)])
			->assertJsonValidationErrors('description');
	}

	public function test_asserts_category_type_field_is_required(): void
	{
		$this->actingAs($this->user)
			->postJson($this->url)
			->assertJsonValidationErrors('category_type');
	}

	public function test_asserts_only_valid_value_allowed_in_category_type(): void
	{
		$this->actingAs($this->user)
			->postJson($this->url, [
				'category_type' => 9999999,
			])
			->assertJsonValidationErrors('category_type');
	}

	public function test_asserts_parent_field_is_optional(): void
	{
		$this->actingAs($this->user)
			->postJson($this->url)
			->assertJsonMissingValidationErrors('parent_id');
	}

	public function test_asserts_parent_id_is_valid(): void
	{
		$this->actingAs($this->user)
			->postJson($this->url, ['parent_id' => 9999999])
			->assertJsonValidationErrors('parent_id');
	}

	public function test_asserts_only_own_category_can_be_attached_to_parent_field(): void
	{
		/** @var Category $anotherUserCategory */
		$anotherUserCategory = Category::factory()
			->for(User::factory()->create())
			->setCategoryType(CategoryTypeState::Debt)
			->create();

		$this->actingAs($this->user)
			->postJson($this->url, [
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
			->postJson($this->url, [
				'category_type' => CategoryTypeState::Income->value,
				'parent_id' => $anotherCategory->id,
			])
			->assertJsonValidationErrors('parent_id');
	}

	public function test_asserts_user_can_create_category(): void
	{
		$attributes = [
			'name' => $this->faker->word,
			'description' => $this->faker->sentence,
			'category_type' => CategoryTypeState::Debt->value,
		];

		$this->actingAs($this->user)
			->postJson($this->url, $attributes)
			->assertCreated();

		$this->assertDatabaseCount('categories', 1);
		$this->assertDatabaseHas('categories', [
			'name' => $attributes['name'],
			'category_type' => $attributes['category_type'],
		]);
	}

	public function test_asserts_user_can_create_category_with_parent(): void
	{
		/** @var Category $anotherCategory */
		$anotherCategory = Category::factory()
			->for($this->user)
			->setCategoryType(CategoryTypeState::Debt)
			->create();

		$attributes = [
			'name' => $this->faker->word,
			'description' => $this->faker->sentence,
			'category_type' => CategoryTypeState::Debt->value,
			'parent_id' => $anotherCategory->id,
		];

		$this->actingAs($this->user)
			->postJson($this->url, $attributes)
			->assertCreated();

		$this->assertDatabaseCount('categories', 2);
		$this->assertDatabaseHas('categories', [
			'name' => $attributes['name'],
			'category_type' => $attributes['category_type'],
			'parent_id' => $attributes['parent_id'],
		]);
	}
}
