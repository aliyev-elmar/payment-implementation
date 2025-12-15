<?php

namespace App\Exceptions;

use Illuminate\Http\Response;
use RuntimeException;

class InvalidOrderStateException extends RuntimeException
{
    /**
     * @var int
     */
    public int $statusCode;

    /**
     * @param string $message
     */
    public function __construct(string $message)
    {
        $this->statusCode = Response::HTTP_BAD_REQUEST;
        parent::__construct("errorCode: Invalid Order State, errorDescription: {$message}");
    }
}
