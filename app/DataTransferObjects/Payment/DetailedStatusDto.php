<?php

namespace App\DataTransferObjects\Payment;

class DetailedStatusDto
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
