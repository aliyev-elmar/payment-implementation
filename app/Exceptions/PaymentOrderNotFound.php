<?php

namespace App\Exceptions;

use RuntimeException;

class PaymentOrderNotFound  extends RuntimeException
{
    /**
     * @var int
     */
    public int $statusCode;

    /**
     * @param string $message
     * @param int $statusCode
     */
    public function __construct(string $message, int $statusCode)
    {
        $this->statusCode = $statusCode;
        parent::__construct($message);
    }

}
