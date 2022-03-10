<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TagPolicy
{
	use HandlesAuthorization;

	/**
	 * Determine whether the user can view the model.
	 *
	 * @param User $user
	 * @param Tag  $tag
	 * @return bool
	 */
	public function view(User $user, Tag $tag): bool
	{
		return $user->id === $tag->user_id;
	}

	/**
	 * Determine whether the user can update the model.
	 *
	 * @param User $user
	 * @param Tag  $tag
	 * @return bool
	 */
	public function update(User $user, Tag $tag): bool
	{
		return $user->id === $tag->user_id;
	}

	/**
	 * Determine whether the user can delete the model.
	 *
	 * @param User $user
	 * @param Tag  $tag
	 * @return bool
	 */
	public function delete(User $user, Tag $tag): bool
	{
		return $user->id === $tag->user_id;
	}
}
