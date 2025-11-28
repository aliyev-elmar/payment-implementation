<?php

namespace App\DataTransferObjects\Payment\Order\SimpleStatus;

use App\DataTransferObjects\Dto;

class SimpleStatusType extends Dto
{
    /**
     * @param string $title
     */
    public function __construct(
        public string $title,
    )
    {
    }
}
