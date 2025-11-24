<?php

namespace App\Repositories;

use App\DTOs\Payment\DetailedStatusDto;
use App\DTOs\Payment\SimpleStatusDto;

interface IPaymentRepository
{
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
}
