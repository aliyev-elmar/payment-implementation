<?php

namespace App\DataTransferObjects\Payment;

class CreateOrderDto
{
    /**
     * @param int $httpCode
     * @param object|null $order
     */
    public function __construct(
        public int $httpCode,
        public ?object $order = null,
    )
    {
    }
}
