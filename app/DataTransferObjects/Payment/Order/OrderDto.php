<?php

namespace App\DataTransferObjects\Payment\Order;

use App\DataTransferObjects\Dto;

class OrderDto extends Dto
{
    /**
     * @param string $id
     * @param string $hppUrl
     * @param string $password
     * @param string $status
     * @param string|null $cvv2AuthStatus
     * @param string|null $secret
     */
    public function __construct(
        public string $id,
        public string $hppUrl,
        public string $password,
        public string $status,
        public ?string $cvv2AuthStatus,
        public ?string $secret,
    )
    {
    }
}
