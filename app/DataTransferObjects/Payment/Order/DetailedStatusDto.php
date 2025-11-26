<?php

namespace App\DataTransferObjects\Payment\Order;

use App\DataTransferObjects\Dto;

class DetailedStatusDto extends Dto
{
    /**
     * @param int $httpCode
     */
    public function __construct(
        public int $httpCode,
    )
    {
    }
}
