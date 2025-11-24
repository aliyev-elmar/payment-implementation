<?php

namespace App\DTOs\Payment;

use App\DTOs\Dto;

class SimpleStatusDto extends Dto
{
    /**
     * @param int $httpCode
     * @param object|null $order
     */
    public function __construct(
        public int $httpCode,
        public ?object $order = null,
    ){
    }
}
