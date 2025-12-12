<?php

namespace App\Services;

use App\DataTransferObjects\Payment\Order\SimpleStatus\SimpleStatusResponseDto;
use App\Enums\Payment\Order\OrderTypeRid;

class PaymentService
{
    /**
     * @param PaymentDriverFactory $paymentDriverFactory
     */
    public function __construct(private readonly PaymentDriverFactory $paymentDriverFactory)
    {
    }

    /**
     * @param string $driver
     * @param OrderTypeRid $orderTypeRid
     * @param int $amount
     * @param string $description
     * @return string|null
     */
    public function createOrder(string $driver, OrderTypeRid $orderTypeRid, int $amount, string $description): ?string
    {
        $gateway = $this->paymentDriverFactory->driver($driver);
        $response = $gateway->createOrder($orderTypeRid, $amount, $description);
        return $response->formUrl;
    }

    /**
     * @param string $driver
     * @param int $orderId
     * @return SimpleStatusResponseDto
     */
    public function getSimpleStatusByOrderId(string $driver, int $orderId): SimpleStatusResponseDto
    {
        $gateway = $this->paymentDriverFactory->driver($driver);
        return $gateway->getSimpleStatusByOrderId($orderId);
    }
}
