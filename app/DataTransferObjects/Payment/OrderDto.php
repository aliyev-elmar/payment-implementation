<?php

namespace App\DataTransferObjects\Payment;

class OrderDto
{
    /**
     * @param string $id
     * @param string $hppUrl
     * @param string $password
     * @param string $status
     */
    public function __construct(
        public string $id,
        public string $hppUrl,
        public string $password,
        public string $status,
    )
    {
    }
}
