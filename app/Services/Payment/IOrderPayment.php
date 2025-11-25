<?php

namespace App\Services\Payment;

use App\DataTransferObjects\Payment\{CreateOrderDto, SimpleStatusDto};

interface IOrderPayment
{
    /**
     * Send order request
     *
     * @param string $typeRid
     * @param float $amount
     * @param string $description
     * @param string $hppRedirectUrl
     * @param string $logPath
     * @return CreateOrderDto
     */
    public function sendRequest(string $typeRid, float $amount, string $description, string $hppRedirectUrl, string $logPath): CreateOrderDto;

    /**
     * @param object $order
     * @return string
     */
    public function getFormUrlByOrder(object $order): string;

    /**
     * @param int $orderId
     * @return SimpleStatusDto
     */
    public function getSimpleStatusByOrderId(int $orderId) : SimpleStatusDto;
}
