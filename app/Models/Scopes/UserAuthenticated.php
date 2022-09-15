<?php

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Schema;

class UserAuthenticated implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  Builder  $builder
     * @param  Model  $model
     * @return void
     *
     * @throws AuthenticationException
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (! auth()->check()) {
            throw new AuthenticationException();
        }

        $userId = auth()->id();
        $tableColumns = Schema::getColumnListing($model->getTable());

        if (in_array('user_id', $tableColumns, true)) {
            $builder->where('user_id', $userId);
        } elseif (method_exists($model, 'users')) {
            $builder->whereHas('users', static function ($query) use ($userId) {
                $query->where('users.id', $userId);
            });
        } elseif (get_class($model) === User::class) {
            $builder->where('id', $userId);
        }
    }
}
