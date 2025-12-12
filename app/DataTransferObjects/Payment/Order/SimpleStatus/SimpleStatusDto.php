<?php

namespace App\DataTransferObjects\Payment\Order\SimpleStatus;

use App\DataTransferObjects\Dto;

class SimpleStatusDto extends Dto
{
    /**
     * @param int $id
     * @param string $typeRid
     * @param string $status
     * @param string $prevStatus
     * @param string $lastStatusLogin
     * @param int $amount
     * @param string $currency
     * @param string $createTime
     * @param string|null $finishTime
     * @param SimpleStatusType $type
     */
    public function __construct(
        public int $id,
        public string $typeRid,
        public string $status,
        public string $prevStatus,
        public string $lastStatusLogin,
        public int $amount,
        public string $currency,
        public string $createTime,
        public ?string $finishTime,
        public SimpleStatusType $type,
    )
    {
    }
}
