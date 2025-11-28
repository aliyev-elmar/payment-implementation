<?php

namespace App\Repositories\Payment;

use App\DataTransferObjects\Payment\Order\CreateOrderResponseDto;
use App\DataTransferObjects\Payment\Order\SimpleStatus\SimpleStatusResponseDto;

abstract class PaymentRepository
{
    /**
     * @param int $amount
     * @param string $description
     * @param string $currency
     * @param string $language
     * @param string $typeRid
     * @param array $hppCofCapturePurposes
     * @return CreateOrderResponseDto
     */
    public abstract function createOrder(
        int $amount,
        string $description,
        string $currency = 'AZN',
        string $language = 'az',
        string $typeRid = 'Purchase',
        array $hppCofCapturePurposes = ['Cit'],
    ): CreateOrderResponseDto;

    /**
     * @param int $orderId
     * @return SimpleStatusResponseDto
     */
    public abstract function getSimpleStatusByOrderId(int $orderId): SimpleStatusResponseDto;
}
