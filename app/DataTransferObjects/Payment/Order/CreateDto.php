<?php

namespace App\DataTransferObjects\Payment\Order;

use App\DataTransferObjects\Dto;

class CreateDto extends Dto
{
    /**
     * @param int $httpCode
     * @param OrderDto $order
     */
    public function __construct(
        public int $httpCode,
        public OrderDto $order,
    )
    {
    }
}
