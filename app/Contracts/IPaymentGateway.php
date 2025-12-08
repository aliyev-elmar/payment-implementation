<?php

namespace App\Contracts;

use App\Enums\Payment\Order\OrderTypeRid;
use App\DataTransferObjects\Payment\Order\CreateOrderResponseDto;
use App\DataTransferObjects\Payment\Order\SimpleStatus\SimpleStatusResponseDto;
use App\Exceptions\{PaymentGatewayException, OrderNotFoundException};

interface IPaymentGateway
{
    /**
     * @param OrderTypeRid $orderTypeRid
     * @param int $amount
     * @param string $description
     * @return CreateOrderResponseDto
     */
    public function createOrder(OrderTypeRid $orderTypeRid, int $amount, string $description): CreateOrderResponseDto;

    /**
     * @param int $orderId
     * @return SimpleStatusResponseDto
     * @throws PaymentGatewayException
     * @throws OrderNotFoundException
     */
    public function getSimpleStatusByOrderId(int $orderId): SimpleStatusResponseDto;
}
