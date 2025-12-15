<?php

namespace App\DataTransferObjects\Payment\Order\SetSourceToken;

use App\DataTransferObjects\Dto;

readonly class SourceTokenCardDto extends Dto
{
    /**
     * @param int|null $expiration
     * @param string|null $brand
     */
    public function __construct(
        public ?int $expiration,
        public ?string $brand,
    )
    {
    }
}
