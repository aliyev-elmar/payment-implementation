<?php

namespace App\Exceptions\Payment;

use RuntimeException;

class PaymentIsRequiredException  extends RuntimeException
{
    /**
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
