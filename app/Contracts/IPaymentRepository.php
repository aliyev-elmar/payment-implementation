<?php

namespace App\Contracts;

use App\DataTransferObjects\Payment\Order\{DetailedStatusDto, SimpleStatusDto};

interface IPaymentRepository
{
    /**
     * @return array
     */
    public function getRequestHeader(): array;

    /**
     * @param int $amount
     * @param string $description
     * @return array
     */
    public function getCreateOrderRequestBody(int $amount, string $description): array;

    /**
     * @param int $orderId
     * @return SimpleStatusDto
     */
    public function getSimpleStatusByOrderId(int $orderId): SimpleStatusDto;

    /**
     * @param int $orderId
     * @return DetailedStatusDto
     */
    public function getDetailedStatusByOrderId(int $orderId): DetailedStatusDto;

    /**
     * @param string $subFolderPath
     * @return string
     */
    public function getLogPath(string $subFolderPath): string;
}
