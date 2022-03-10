<?php

namespace App\Actions\Category;

use App\DataObject\CategoryData;
use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;

class CreateCategory
{
	protected User|null $user;

	/**
	 * @throws AuthenticationException
	 */
	public function __construct(protected CategoryData $attributes)
	{
		if (!auth()->check()) {
			throw new AuthenticationException();
		}

		$this->user = auth()->user();
	}

	public function execute(): Category
	{
		$category = new Category([
			'name' => $this->attributes->name,
			'description' => $this->attributes->description,
			'category_type' => $this->attributes->category_type->value,
		]);

		$category->user()->associate($this->user);

		if ($this->attributes->parent_id) {
			$category->parent()->associate($this->attributes->parent_id);
		}

		$category->save();

		return $category;
	}

	public function setUser(User $user): CreateCategory
	{
		$this->user = $user;

		return $this;
	}
}
