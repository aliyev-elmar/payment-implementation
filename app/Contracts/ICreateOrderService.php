<?php

namespace App\Contracts;

use App\DataTransferObjects\Payment\Order\{CreateDto, OrderDto, SimpleStatusDto};
use App\Exceptions\Payment\CreateOrderException;

interface ICreateOrderService
{
    /**
     * @param float $amount
     * @param string $description
     * @return CreateDto
     * @throws CreateOrderException
     */
    public function create(float $amount, string $description): CreateDto;

    /**
     * @param int $orderId
     * @return bool
     * @throws \Exception
     */
    public function checkStatusById(int $orderId): bool;

    /**
     * @param OrderDto $order
     * @return string
     */
    public function getFormUrlByOrder(OrderDto $order): string;

    /**
     * @param int $orderId
     * @return SimpleStatusDto
     */
    public function getSimpleStatusByOrderId(int $orderId) : SimpleStatusDto;
}
