<?php

namespace App\DTOs\Payment;

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
