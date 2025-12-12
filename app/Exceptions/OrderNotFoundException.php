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

    /**
     * @param string $paymentGateway
     */
    public function __construct(string $paymentGateway)
    {
        $this->statusCode = Response::HTTP_NOT_FOUND;
        parent::__construct("Order not found on $paymentGateway");
    }
}
