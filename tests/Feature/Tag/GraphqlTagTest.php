<?php

namespace Tests\Feature\Tag;

use App\Models\Scopes\UserAuthenticated;
use App\Models\Tag;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\BaseGraphqlTest;

class GraphqlTagTest extends BaseGraphqlTest
{
    public User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootRefreshesSchemaCache();

        $this->user = User::factory()->create();

        Tag::factory()
            ->for($this->user)
            ->create();
    }

    public function test_asserts_guest_not_allowed(): void
    {
        $this->graphQL(' { tag(id: 1) { id name } } ')
            ->assertOk()
            ->assertJson([
                'data' => ['tag' => null],
            ]);

        $this->graphQL('{ tags(first: 5, page: 1) { data { id name } } }')
            ->assertOk()
            ->assertJson([
                'data' => ['tags' => null],
            ]);
    }

    public function test_asserts_user_gets_list_of_tags(): void
    {
        $this->actingAs($this->user)
            ->graphQL(
                '{ tags(first: 5, page: 1) { data { id name description created_at updated_at } paginatorInfo { count currentPage firstItem hasMorePages lastItem lastPage perPage total } } }'
            )
            ->assertOk()
            ->assertJsonStructure($this->getResponseStructure('tags'));
    }

    public function test_asserts_user_can_get_specific_tag_data(): void
    {
        $tagId = $this->getRandomTagId();

        $this->actingAs($this->user)
            ->graphQL(
                "{ tag(id: $tagId) { id name description created_at updated_at } }"
            )
            ->assertOk()
            ->assertJsonStructure($this->getResponseStructure('tag'));
    }

    public function test_asserts_user_can_get_related_data(): void
    {
        $tagId = $this->getRandomTagId();

        $this->actingAs($this->user)
            ->graphQL("{ tag(id: $tagId) { user { id email } } }")
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'tag' => [
                        'user' => ['id', 'email'],
                    ],
                ],
            ]);
    }

    public function test_asserts_user_can_only_access_own_data(): void
    {
        $tagId = Tag::factory()
            ->for(User::factory())
            ->create()
            ->getAttribute('id');

        $this->actingAs($this->user)
            ->graphQL("{ tag(id: $tagId) { id name user { id email } } }")
            ->assertJson([
                'data' => [
                    'tag' => null,
                ],
            ]);
    }

    public function getItemStructure(): array
    {
        return ['id', 'name', 'description'];
    }

    private function getRandomTagId()
    {
        return Tag::withoutGlobalScope(UserAuthenticated::class)
            ->where('user_id', $this->user->id)
            ->inRandomOrder()
            ->firstOrFail()
            ->getAttribute('id');
    }
}
