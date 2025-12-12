<?php

namespace App\DataTransferObjects\Payment\Order\SetSourceToken;

use App\DataTransferObjects\Dto;

class SetSourceTokenDto extends Dto
{
    /**
     * @param string $status
     * @param string $cvv2AuthStatus
     * @param string $tdsV1AuthStatus
     * @param string $tdsV2AuthStatus
     * @param string $otpAutStatus
     * @param SourceTokenDto $srcToken
     */
    public function __construct(
        public string $status,
        public string $cvv2AuthStatus,
        public string $tdsV1AuthStatus,
        public string $tdsV2AuthStatus,
        public string $otpAutStatus,
        public SourceTokenDto $srcToken,
    )
    {
    }
}
