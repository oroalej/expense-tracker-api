<?php

namespace Tag;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class UpdateTagTest extends TestCase
{
	public Tag $tag;

	public string $url;

	public User $user;

	protected function setUp(): void
	{
		parent::setUp();

		$this->user = User::factory()->create();
		$this->tag = Tag::factory()
			->for($this->user)
			->create();

		$this->url = "api/tag/{$this->tag->id}";
	}

	public function test_asserts_guest_not_allowed(): void
	{
		$this->putJson($this->url)->assertStatus(Response::HTTP_UNAUTHORIZED);
	}

	public function test_asserts_name_field_not_too_long(): void
	{
		$this->actingAs($this->user)
			->putJson($this->url, ['name' => Str::random(200)])
			->assertJsonValidationErrors('name');
	}

	public function test_asserts_name_field_is_required(): void
	{
		$this->actingAs($this->user)
			->putJson($this->url, [])
			->assertJsonValidationErrors('name');
	}

	public function test_asserts_description_field_is_optional(): void
	{
		$this->actingAs($this->user)
			->putJson($this->url)
			->assertJsonMissingValidationErrors('description');
	}

	public function test_asserts_description_field_is_not_too_long(): void
	{
		$this->actingAs($this->user)
			->putJson($this->url, ['description' => Str::random(192)])
			->assertJsonValidationErrors('description');
	}

	public function test_asserts_only_owner_can_update_data(): void
	{
		$this->actingAs(User::factory()->create())
			->putJson($this->url, [
				'name' => Str::random(),
				'description' => Str::random(),
			])
			->assertStatus(Response::HTTP_NOT_FOUND);
	}

	public function test_asserts_user_can_update_own_data(): void
	{
		$this->actingAs($this->user)
			->putJson($this->url, [
				'name' => Str::random(),
				'description' => Str::random(),
			])
			->assertStatus(Response::HTTP_OK);
	}
}
