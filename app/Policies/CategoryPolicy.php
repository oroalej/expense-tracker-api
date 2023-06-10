<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
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
     * @return bool
     */
    public function store(User $user): bool
    {
        return $user->id === $this->ledger->user_id;
    }

    /**
     * @param  User  $user
     * @param  Category  $category
     * @return bool
     */
    public function view(User $user, Category $category): bool
    {
        return $category->ledger_id === $this->ledger->id &&
            $user->id === $this->ledger->user_id;
    }

    /**
     * @param  User  $user
     * @param  Category  $category
     * @return bool
     */
    public function update(User $user, Category $category): bool
    {
        return $category->ledger_id === $this->ledger->id &&
            $user->id === $this->ledger->user_id;
    }

    /**
     * @param  User  $user
     * @param  Category  $category
     * @return bool
     */
    public function delete(User $user, Category $category): bool
    {
        return $category->ledger_id === $this->ledger->id &&
            $user->id === $this->ledger->user_id;
    }
}
