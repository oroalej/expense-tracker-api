<?php

namespace App\Policies;

use App\Models\CategoryGroup;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryGroupPolicy
{
    use HandlesAuthorization;

    protected object $ledger;

    public function __construct()
    {
        $this->ledger = (object) request()->request->get('ledger', [
            'id' => null,
            'user_id' => null
        ]);
    }

    /**
     * @param  User  $user
     * @param  CategoryGroup  $categoryGroup
     * @return bool
     */
    public function view(User $user, CategoryGroup $categoryGroup): bool
    {
        return $user->id === $this->ledger->user_id &&
            $categoryGroup->ledger_id === $this->ledger->id;
    }

    /**
     * @param  User  $user
     * @return bool
     */
    public function store(User $user): bool
    {
        return $user->id === $this->ledger->user_id;
    }

    /**
     * @param  User  $user
     * @param  CategoryGroup  $categoryGroup
     * @return bool
     */
    public function update(User $user, CategoryGroup $categoryGroup): bool
    {
        return $user->id === $this->ledger->user_id &&
            $categoryGroup->ledger_id === $this->ledger->id;
    }

    /**
     * @param  User  $user
     * @param  CategoryGroup  $categoryGroup
     * @return bool
     */
    public function delete(User $user, CategoryGroup $categoryGroup): bool
    {
        return $user->id === $this->ledger->user_id &&
            $categoryGroup->ledger_id === $this->ledger->id;
    }
}
