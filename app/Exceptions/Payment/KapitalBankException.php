<?php

namespace App\Exceptions\Payment;

use RuntimeException;

class KapitalBankException  extends RuntimeException
{
    /**
     * @var int
     */
    public int $statusCode;

    /**
     * @param int $statusCode
     * @param string|null $errorCode
     * @param string|null $errorDescription
     */
    public function __construct(int $statusCode, ?string $errorCode, ?string $errorDescription)
    {
        $this->statusCode = $statusCode;
        parent::__construct("errorCode: {$errorCode}, errorDescription: {$errorDescription}");
    }

}
