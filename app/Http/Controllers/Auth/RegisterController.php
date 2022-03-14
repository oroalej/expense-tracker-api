<?php

namespace App\Http\Controllers\Auth;

use App\Actions\User\CreateUser;
use App\DataObject\UserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  RegisterRequest  $request
     * @return JsonResponse
     */
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        (new CreateUser(
            new UserData($request->name, $request->email, $request->password)
        ))->execute();

        return response()->json([], Response::HTTP_CREATED);
    }
}
