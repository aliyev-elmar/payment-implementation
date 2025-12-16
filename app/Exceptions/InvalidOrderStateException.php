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
     * @param int $statusCode
     */
    public function __construct(string $message, int $statusCode = Response::HTTP_BAD_REQUEST)
    {
        $this->statusCode = $statusCode;
        parent::__construct("errorCode: Invalid Order State, errorDescription: {$message}");
    }
}
