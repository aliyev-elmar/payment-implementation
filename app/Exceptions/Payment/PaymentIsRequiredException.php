<?php

namespace App\Exceptions\Payment;

use RuntimeException;

class PaymentIsRequiredException  extends RuntimeException
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
        parent::__construct($message);
    }

}
