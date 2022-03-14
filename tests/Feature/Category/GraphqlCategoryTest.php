<?php

namespace Tests\Feature\Category;

use App\Enums\CategoryTypeState;
use App\Models\Category;
use App\Models\Scopes\UserAuthenticated;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\BaseGraphqlTest;

class GraphqlCategoryTest extends BaseGraphqlTest
{
    public User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootRefreshesSchemaCache();

        $this->user = User::factory()->create();

        $this->category = Category::factory()
            ->for($this->user)
            ->create();
    }

    public function test_asserts_guest_are_not_allowed(): void
    {
        $this->graphQL(' { category(id: 1) { id name } } ')
            ->assertOk()
            ->assertJson([
                'data' => ['category' => null],
            ]);

        $this->graphQL('{ categories(first: 5, page: 1) { data { id name } } }')
            ->assertOk()
            ->assertJson([
                'data' => ['categories' => null],
            ]);
    }

    public function test_asserts_user_gets_list_of_categories(): void
    {
        $this->actingAs($this->user)
            ->graphQL(
                "{ categories(first: 5, page: 1) { data { {$this->getStringItemStructure()} } paginatorInfo { {$this->getStringPaginationStructure()} } } }"
            )
            ->assertOk()
            ->assertJsonStructure($this->getResponseStructure('categories'));
    }

    public function test_asserts_user_can_get_specific_category_data(): void
    {
        $this->actingAs($this->user)
            ->graphQL(
                "{ category(id: 3) { {$this->getStringItemStructure()} } }"
            )
            ->assertOk()
            ->assertJsonStructure($this->getResponseStructure('category'));
    }

    public function test_asserts_user_can_get_related_data(): void
    {
        $this->actingAs($this->user)
            ->graphQL('{ category(id: 4) { user { id email } } }')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'category' => [
                        'user' => ['id', 'email'],
                    ],
                ],
            ]);
    }

    public function test_asserts_user_can_only_access_own_data(): void
    {
        $categoryId = Category::factory()
            ->for(User::factory())
            ->create()
            ->getAttribute('id');

        $this->actingAs($this->user)
            ->graphQL("{ category(id: $categoryId) { user { id email } } }")
            ->assertJson([
                'data' => [
                    'category' => null,
                ],
            ]);
    }

    public function test_asserts_query_returned_correct_filtered_data(): void
    {
        $limit = 2;
        $categoryType = Category::withoutGlobalScope(UserAuthenticated::class)
            ->where('user_id', $this->user->id)
            ->inRandomOrder()
            ->first()
            ->getAttribute('category_type');

        $categories = Category::withoutGlobalScope(UserAuthenticated::class)
            ->select('category_type')
            ->where('user_id', $this->user->id)
            ->where('category_type', $categoryType)
            ->limit($limit)
            ->get()
            ->toArray();

        $categoryTypeName = CategoryTypeState::tryFrom($categoryType)->name;

        $this->actingAs($this->user)
            ->graphQL(
                "{ categories(first: $limit, page: 1, category_type: $categoryTypeName) { data { category_type } } }"
            )
            ->assertExactJson([
                'data' => [
                    'categories' => ['data' => $categories],
                ],
            ]);
    }

    public function getItemStructure(): array
    {
        return [
            'id',
            'category_type',
            'name',
            'description',
            'is_default',
            'is_editable',

            'parent_id',
            'user_id',

            'created_at',
            'updated_at',
        ];
    }
}
