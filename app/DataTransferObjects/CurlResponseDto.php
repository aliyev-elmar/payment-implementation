<?php

namespace App\DataTransferObjects;

readonly class CurlResponseDto extends Dto
{
    /**
     * @param object|null $response
     * @param int $httpCode
     * @param string $curlError
     * @param int $curlErrno
     */
    public function __construct(
        public ?object $response,
        public int $httpCode,
        public string $curlError,
        public int $curlErrno,
    )
    {
    }
}
