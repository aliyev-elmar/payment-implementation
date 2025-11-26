<?php

namespace App\Repositories\Payment;

use App\Contracts\IOrderCreator;
use App\DataTransferObjects\Payment\Order\{DetailedStatusDto, SimpleStatusDto};

abstract class PaymentRepository implements IOrderCreator
{
    /**
     * @return array
     */
    public abstract function getRequestHeader(): array;

    /**
     * @param int $orderId
     * @return SimpleStatusDto
     */
    public abstract function getSimpleStatusByOrderId(int $orderId): SimpleStatusDto;

    /**
     * @param int $orderId
     * @return DetailedStatusDto
     */
    public abstract function getDetailedStatusByOrderId(int $orderId): DetailedStatusDto;

    /**
     * @param string $subFolderPath
     * @return string
     */
    public abstract function getLogPath(string $subFolderPath): string;
}
