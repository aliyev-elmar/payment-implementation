<?php

namespace App\Services;

use App\Enums\Payment\Order\OrderTypeRid;
use App\DataTransferObjects\Payment\Order\CreateOrderResponseDto;
use App\DataTransferObjects\Payment\Order\SetSourceToken\SetSourceTokenResponseDto;
use App\DataTransferObjects\Payment\Order\SimpleStatus\SimpleStatusResponseDto;
use App\Exceptions\{
    InvalidOrderStateException,
    InvalidRequestException,
    InvalidTokenException,
    OrderNotFoundException,
};

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
     * @return CreateOrderResponseDto
     * @throws InvalidRequestException
     */
    public function createOrder(string $driver, OrderTypeRid $orderTypeRid, int $amount, string $description): CreateOrderResponseDto
    {
        $gateway = $this->paymentDriverFactory->driver($driver);
        return $gateway->createOrder($orderTypeRid, $amount, $description);
    }

    /**
     * @param string $driver
     * @param int $orderId
     * @param string $orderPassword
     * @return SetSourceTokenResponseDto
     * @throws InvalidTokenException
     * @throws InvalidRequestException
     * @throws InvalidOrderStateException
     */
    public function setSourceToken(string $driver, int $orderId, string $orderPassword): SetSourceTokenResponseDto
    {
        $gateway = $this->paymentDriverFactory->driver($driver);
        return $gateway->setSourceToken($orderId, $orderPassword);
    }

    /**
     * @param string $driver
     * @param int $orderId
     * @return SimpleStatusResponseDto
     * @throws InvalidRequestException
     * @throws OrderNotFoundException
     */
    public function getSimpleStatusByOrderId(string $driver, int $orderId): SimpleStatusResponseDto
    {
        $gateway = $this->paymentDriverFactory->driver($driver);
        return $gateway->getSimpleStatusByOrderId($orderId);
    }
}
