<?php

namespace Tag;

use App\Models\User;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class StoreTagTest extends TestCase
{
	public string $url = 'api/tag';

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

	public function test_asserts_name_field_not_too_long(): void
	{
		$this->actingAs($this->user)
			->postJson($this->url, ['name' => Str::random(192)])
			->assertJsonValidationErrors('name');
	}

	public function test_asserts_name_field_is_required(): void
	{
		$this->actingAs($this->user)
			->postJson($this->url, [])
			->assertJsonValidationErrors('name');
	}

	public function test_asserts_description_field_is_not_too_long(): void
	{
		$this->actingAs($this->user)
			->postJson($this->url, ['description' => Str::random(192)])
			->assertJsonValidationErrors('description');
	}

	public function test_asserts_description_field_is_optional(): void
	{
		$this->actingAs($this->user)
			->postJson($this->url)
			->assertJsonMissingValidationErrors('description');
	}

	public function test_asserts_user_can_create_tag(): void
	{
		$attributes = [
			'name' => $this->faker->word,
			'description' => $this->faker->sentence,
		];

		$this->actingAs($this->user)
			->postJson($this->url, $attributes)
			->assertCreated();
	}

	public function test_asserts_created_data_is_in_database(): void
	{
		$attributes = [
			'name' => $this->faker->word,
			'description' => $this->faker->sentence,
		];

		$this->actingAs($this->user)
			->postJson($this->url, $attributes)
			->assertCreated();

		$this->assertDatabaseHas('tags', $attributes);
	}
}
