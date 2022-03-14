<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use function response;

class AuthController extends Controller
{
    /**
     * @param  LoginRequest  $request
     * @return string
     * @throws ValidationException
     */
    public function token(LoginRequest $request): string
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        return $user->createToken(
            $request->header('user-agent')
        )->plainTextToken;
    }

    /**
     * @param  Request  $request
     * @return Response
     */
    public function logout(Request $request): Response
    {
        $request
            ->user()
            ->currentAccessToken()
            ->delete();

        return response()->noContent();
    }
}
