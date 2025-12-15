<?php

namespace App\DataTransferObjects\Payment\Order\SetSourceToken;

use App\DataTransferObjects\Dto;

readonly class SetSourceTokenResponseDto extends Dto
{
    /**
     * @param int $httpCode
     * @param SetSourceTokenDto|null $order
     * @param string|null $curlError
     * @param int|null $curlErrno
     */
    public function __construct(
        public int $httpCode,
        public ?SetSourceTokenDto $order = null,
        public ?string $curlError = null,
        public ?int $curlErrno = null,
    )
    {
    }
}
