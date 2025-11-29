<?php

namespace App\DataTransferObjects\Payment\Order\SimpleStatus;

class SimpleStatusResponseDto
{
    /**
     * @param int $httpCode
     * @param SimpleStatusDto|null $order
     * @param string|null $curlError
     * @param string|null $curlErrno
     */
    public function __construct(
        public int $httpCode,
        public ?SimpleStatusDto $order = null,
        public ?string $curlError = null,
        public ?string $curlErrno = null,
    )
    {
    }
}
