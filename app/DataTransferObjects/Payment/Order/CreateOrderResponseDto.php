<?php

namespace App\DataTransferObjects\Payment\Order;

use App\DataTransferObjects\Dto;

class CreateOrderResponseDto extends Dto
{
    /**
     * @param int $httpCode
     * @param CreateOrderDto|null $order
     * @param string|null $curlError
     * @param int|null $curlErrno
     * @param string|null $formUrl
     */
    public function __construct(
        public int $httpCode,
        public ?CreateOrderDto $order = null,
        public ?string $curlError = null,
        public ?int $curlErrno = null,
        public ?string $formUrl = null,
    )
    {
    }
}
