<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LoginException extends Exception
{
    public function __construct(
        protected $message = 'These credentials do not match our records',
        protected $code = Response::HTTP_UNPROCESSABLE_ENTITY,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json(
                [
                    'message' => $this->getMessage(),
                ],
                $this->getCode()
            );
        }
    }
}
