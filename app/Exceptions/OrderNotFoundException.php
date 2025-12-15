<?php

namespace App\Exceptions;

use Illuminate\Http\Response;
use RuntimeException;

class OrderNotFoundException extends RuntimeException
{
    /**
     * @var int
     */
    public int $statusCode;

    public function __construct()
    {
        $this->statusCode = Response::HTTP_NOT_FOUND;
        parent::__construct('Order not found');
    }
}
