<?php

namespace App\Http\Controllers\Auth;

use App\DTO\Auth\EmailAndPasswordDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\LedgerResource;
use App\Http\Resources\UserResource;
use App\Repository\Auth\EmailAndPasswordRepository;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RegisterController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  RegisterRequest  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $user = (new EmailAndPasswordRepository())->register(
            new EmailAndPasswordDTO(
                email: $request->validated('email'),
                password: $request->validated('password')
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
        ], Response::HTTP_CREATED);
    }
}
