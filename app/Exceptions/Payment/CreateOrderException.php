<?php

namespace App\Exceptions\Payment;

use RuntimeException;

class CreateOrderException  extends RuntimeException
{
    /**
     * @var int
     */
    public int $statusCode;

    /**
     * @param string $typeRid
     * @param int $statusCode
     */
    public function __construct(string $typeRid, int $statusCode)
    {
        $this->statusCode = $statusCode;
        parent::__construct("Create Order ($typeRid) Prosesi zamanı xəta baş verdi");
    }

}
