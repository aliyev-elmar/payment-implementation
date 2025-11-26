<?php

namespace App\Exceptions\Payment;

use RuntimeException;

class GetOrderStatusException  extends RuntimeException
{
    /**
     * @var int
     */
    public int $statusCode;

    /**
     * @param int $statusCode
     * @param string $message
     */
    public function __construct(int $statusCode, string $message = 'Get Order Simple Status Prosesi zamanı xəta baş verdi')
    {
        $this->statusCode = $statusCode;
        parent::__construct($message);
    }

}
