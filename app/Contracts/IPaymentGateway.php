<?php

namespace App\Contracts;

use App\Enums\Payment\Order\OrderTypeRid;
use App\DataTransferObjects\Payment\Order\CreateOrderResponseDto;
use App\DataTransferObjects\Payment\Order\SimpleStatus\SimpleStatusResponseDto;
use App\Exceptions\{PaymentGatewayException, OrderNotFoundException};

interface IPaymentGateway
{
    /**
     * @param int $amount
     * @param string $description
     * @param OrderTypeRid $orderTypeRid
     * @return CreateOrderResponseDto
     * @throws PaymentGatewayException
     */
    public function createOrder(int $amount, string $description, OrderTypeRid $orderTypeRid): CreateOrderResponseDto;

    /**
     * @param int $orderId
     * @return SimpleStatusResponseDto
     * @throws PaymentGatewayException
     * @throws OrderNotFoundException
     */
    public function getSimpleStatusByOrderId(int $orderId): SimpleStatusResponseDto;
}
