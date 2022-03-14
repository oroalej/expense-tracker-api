<?php

namespace App\Actions\User;

use App\DataObject\UserData;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Throwable;

class CreateUser
{
    public function __construct(protected UserData $attributes)
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(): User
    {
        $user = User::create([
            'name' => $this->attributes->name,
            'email' => $this->attributes->email,
            'password' => Hash::make($this->attributes->password),
        ]);

        event(new Registered($user));

        return $user;
    }
}
