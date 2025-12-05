<?php

namespace App\DataTransferObjects\Payment\Order\SimpleStatus;

use App\DataTransferObjects\Dto;

class SimpleStatusResponseDto extends Dto
{
    /**
     * @param int $httpCode
     * @param SimpleStatusDto|null $order
     * @param string|null $curlError
     * @param int|null $curlErrno
     */
    public function __construct(
        public int $httpCode,
        public ?SimpleStatusDto $order = null,
        public ?string $curlError = null,
        public ?int $curlErrno = null,
    )
    {
    }
}
