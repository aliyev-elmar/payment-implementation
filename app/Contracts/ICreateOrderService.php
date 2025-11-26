<?php

namespace App\Contracts;

use App\Exceptions\Payment\GetOrderStatusException;
use App\DataTransferObjects\Payment\Order\{CreateDto, OrderDto};
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
     * @throws GetOrderStatusException
     */
    public function checkSimpleStatusById(int $orderId): bool;

    /**
     * @param OrderDto $order
     * @return string
     */
    public function getFormUrlByOrder(OrderDto $order): string;
}
