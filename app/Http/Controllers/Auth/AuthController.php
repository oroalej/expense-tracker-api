<?php

namespace App\Http\Controllers\Auth;

use App\DTO\Auth\EmailAndPasswordDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\LedgerResource;
use App\Http\Resources\UserResource;
use App\Repository\Auth\EmailAndPasswordRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as StatusResponse;

class AuthController extends Controller
{
    /**
     * @param  LoginRequest  $request
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function token(LoginRequest $request): JsonResponse
    {
        $user = (new EmailAndPasswordRepository())->login(
            new EmailAndPasswordDTO(
                email: $request->validated('email'),
                password: $request->validated('password'),
            )
        );

        $token = $user->createToken($request->header('user-agent'))
            ->plainTextToken;

        $ledgers = $user->ledgers()
            ->with('currency:id,name,abbr,code,locale')
            ->orderBy('updated_at')
            ->get();

        return $this->apiResponse([
            'data' => [
                'user'    => new UserResource($user),
                'ledgers' => LedgerResource::collection($ledgers),
                'token'   => $token,
            ],
        ]);
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request
            ->user()
            ->currentAccessToken()
            ->delete();

        return $this->apiResponse([], StatusResponse::HTTP_NO_CONTENT);
    }
}
