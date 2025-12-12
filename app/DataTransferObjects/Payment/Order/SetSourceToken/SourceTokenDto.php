<?php

namespace App\DataTransferObjects\Payment\Order\SetSourceToken;

use App\DataTransferObjects\Dto;

class SourceTokenDto extends Dto
{
    /**
     * @param int $id
     * @param string $paymentMethod
     * @param string $role
     * @param string $status
     * @param string $regTime
     * @param string $displayName
     * @param SourceTokenCardDto $card
     */
    public function __construct(
        public int $id,
        public string $paymentMethod,
        public string $role,
        public string $status,
        public string $regTime,
        public string $displayName,
        public SourceTokenCardDto $card
    )
    {
    }
}
