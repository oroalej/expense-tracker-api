<?php

namespace App\Repository\Auth;

use App\DTO\Auth\EmailAndPasswordDTO;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Throwable;

class EmailAndPasswordRepository
{
    /**
     * @param  EmailAndPasswordDTO  $attributes
     * @return User
     * @throws ValidationException
     */
    public function login(EmailAndPasswordDTO $attributes): User
    {
        $user = User::where('email', $attributes->email)->first();

        if (! $user || ! Hash::check($attributes->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        return $user;
    }

    /**
     * @param  EmailAndPasswordDTO  $attributes
     * @return User
     * @throws Throwable
     */
    public function register(EmailAndPasswordDTO $attributes): User
    {
        return DB::transaction(static function () use ($attributes) {
            $user = User::create([
                'email'    => $attributes->email,
                'password' => Hash::make($attributes->password),
            ]);

            event(new Registered($user));

            return $user;
        });
    }
}
