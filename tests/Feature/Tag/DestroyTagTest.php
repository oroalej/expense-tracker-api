<?php

namespace Tag;

use App\Models\Tag;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class DestroyTagTest extends TestCase
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
		$this->deleteJson($this->url)->assertStatus(
			Response::HTTP_UNAUTHORIZED
		);
	}

	public function test_asserts_only_owner_can_delete_data(): void
	{
		$this->actingAs(User::factory()->create())
			->deleteJson($this->url)
			->assertStatus(Response::HTTP_NOT_FOUND);
	}

	public function test_asserts_owner_can_delete_data(): void
	{
		$this->actingAs($this->user)
			->deleteJson($this->url)
			->assertStatus(Response::HTTP_OK);
	}

	public function test_asserts_deleted_data_not_existing_in_database(): void
	{
		$this->actingAs($this->user)
			->deleteJson($this->url)
			->assertStatus(Response::HTTP_OK);

		$this->assertSoftDeleted('tags', [
			'id' => $this->tag->id,
		]);
	}
}
