<?php

namespace App\Services;

use App\DataTransferObjects\Payment\Order\SimpleStatus\SimpleStatusResponseDto;
use App\Enums\Payment\Order\OrderTypeRid;

class OrderService
{
    /**
     * @param PaymentService $paymentService
     */
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    /**
     * @param string $driver
     * @param OrderTypeRid $orderTypeRid
     * @param int $amount
     * @param string $description
     * @return string|null
     */
    public function create(string $driver, OrderTypeRid $orderTypeRid, int $amount, string $description): ?string
    {
        return $this->paymentService->createOrder(
            $driver,
            $orderTypeRid,
            $amount,
            $description,
        );
    }

    /**
     * @param string $driver
     * @param int $orderId
     * @return SimpleStatusResponseDto
     */
    public function getSimpleStatusByOrderId(string $driver, int $orderId): SimpleStatusResponseDto
    {
        return $this->paymentService->getSimpleStatusByOrderId($driver, $orderId);
    }
}
