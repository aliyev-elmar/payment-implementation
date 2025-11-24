<?php

namespace App\DTOs;

class CurlResponseDto extends Dto
{
    /**
     * @param object|null $response
     * @param int $httpCode
     * @param string $curlError
     * @param string $curlErrno
     */
    public function __construct(
        public ?object $response,
        public int $httpCode,
        public string $curlError,
        public string $curlErrno,
    )
    {
    }
}
