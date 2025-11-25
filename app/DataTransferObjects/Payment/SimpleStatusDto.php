<?php

namespace App\DataTransferObjects\Payment;

use App\DataTransferObjects\Dto;

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
