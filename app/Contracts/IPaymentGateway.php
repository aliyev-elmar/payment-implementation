<?php

namespace App\Contracts;

use App\Enums\Payment\Order\OrderTypeRid;
use App\DataTransferObjects\Payment\Order\CreateOrderResponseDto;
use App\DataTransferObjects\Payment\Order\SetSourceToken\SetSourceTokenResponseDto;
use App\DataTransferObjects\Payment\Order\SimpleStatus\SimpleStatusResponseDto;
use App\Exceptions\OrderNotFoundException;

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
     * @param string $orderPassword
     * @return SetSourceTokenResponseDto
     */
    public function setSourceToken(int $orderId, string $orderPassword): SetSourceTokenResponseDto;

    /**
     * @param int $orderId
     * @return SimpleStatusResponseDto
     * @throws OrderNotFoundException
     */
    public function getSimpleStatusByOrderId(int $orderId): SimpleStatusResponseDto;
}
