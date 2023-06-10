<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as StatusResponse;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    public function apiResponse(
        $data = [],
        $statusCode = StatusResponse::HTTP_OK
    ): JsonResponse {
        $responseStructure = [
            'success' => $data['success'] ?? true,
            'message' => $data['message'] ?? null,
        ];

        if (isset($data['data']) && $statusCode !== StatusResponse::HTTP_NO_CONTENT) {
            $responseStructure['result'] = $data['data'];
        }

        if (isset($data['errors'])) {
            $responseStructure['errors'] = $data['errors'];
        }

        return Response::json($responseStructure, $statusCode);
    }
}
