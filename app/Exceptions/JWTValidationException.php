<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JWTValidationException extends Exception
{
    protected bool $withoutRefreshCookie = false;

    public function __construct(string $message = "", int $code = 0, \Throwable|null $previous = null, $withoutRefreshCookie = false)
    {
        $this->withoutRefreshCookie = $withoutRefreshCookie;

        parent::__construct($message, $code, $previous);
    }

    public function render(Request $request): Response
    {
        $response = response(['message' => $this->getMessage()], $this->getCode());

        if ($this->withoutRefreshCookie) {
            $response->withoutCookie('refresh');
        }

        return $response;
    }
}
