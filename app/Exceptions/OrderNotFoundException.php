<?php

namespace App\Exceptions;

use RuntimeException;

class OrderNotFoundException  extends RuntimeException
{
    /**
     * @param string $paymentGateway
     */
    public function __construct(string $paymentGateway)
    {
        parent::__construct("Order not found on $paymentGateway");
    }
}
