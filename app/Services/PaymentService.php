<?php

namespace App\Services;

use App\Contracts\ILogger;
use App\Enums\Payment\Order\OrderTypeRid;
use App\DataTransferObjects\Payment\Order\SimpleStatus\SimpleStatusResponseDto;

class PaymentService
{
    /**
     * @param ILogger $logService
     * @param PaymentDriverFactory $paymentDriverFactory
     */
    public function __construct(
        private readonly ILogger              $logService,
        private readonly PaymentDriverFactory $paymentDriverFactory,
    )
    {
    }

    /**
     * @param string $driver
     * @param int $amount
     * @param string $description
     * @param OrderTypeRid $orderTypeRid
     * @return string|null
     */
    public function createOrder(string $driver, int $amount, string $description, OrderTypeRid $orderTypeRid): ?string
    {
        $gateway = $this->paymentDriverFactory->driver($driver);
        $response = $gateway->createOrder($amount, $description, $orderTypeRid);
        $order = $response->order;

        $logText  = "OrderId : {$order?->id}, ";
        $logText .= "httpCode : {$response->httpCode}, ";
        $logText .= "Curl Error : {$response->curlError}, ";
        $logText .= "Curl Errno : {$response->curlErrno}, ";
        $logText .= "hppUrl : {$order?->hppUrl}, ";
        $logText .= "status : {$order?->status}, ";
        $logText .= "cvv2AuthStatus : {$order?->cvv2AuthStatus}, ";
        $this->logService->log($response->logFolderPath, $logText);

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
        $response = $gateway->getSimpleStatusByOrderId($orderId);
        $order = $response->order;

        $logText  = "OrderId : {$order?->id}, ";
        $logText .= "httpCode : {$response->httpCode}, ";
        $logText .= "Curl Error : {$response->curlError}, ";
        $logText .= "Curl Errno : {$response->curlErrno}, ";
        $logText .= "status : {$order?->status}, ";
        $this->logService->log($response->logFolderPath, $logText);

        return $response;
    }
}
