<?php

namespace App\Exceptions;

use RuntimeException;

class PaymentGatewayException  extends RuntimeException
{
    /**
     * @var int
     */
    public int $statusCode;

    /**
     * @param string $paymentGateway
     * @param int $statusCode
     * @param string|null $errorCode
     * @param string|null $errorDescription
     */
    public function __construct(string $paymentGateway, int $statusCode, ?string $errorCode, ?string $errorDescription)
    {
        $this->statusCode = $statusCode;
        parent::__construct("`errorCode: {$errorCode}, errorDescription: {$errorDescription}` on {$paymentGateway}");
    }

}
