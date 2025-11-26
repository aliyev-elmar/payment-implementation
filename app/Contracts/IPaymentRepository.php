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
     * @return string
     */
    public function getTypeRid(): string;

    /**
     * @return string
     */
    public function getHppRedirectUrl(): string;

    /**
     * @return string
     */
    public function getCurrency(): string;

    /**
     * @return string
     */
    public function getLanguage(): string;

    /**
     * @param string $subFolderPath
     * @return string
     */
    public function getLogPath(string $subFolderPath): string;

    /**
     * @return array
     */
    public function getHppCofCapturePurposes(): array;
}
