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
        private readonly ILogger $logService,
        private readonly PaymentDriverFactory $paymentDriverFactory,
    )
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
        $order = $response->order;

        $this->logService->log("Payment/{$driver}/CreateOrder/{$orderTypeRid->value}", [
            'OrderId' => $order?->id,
            'httpCode' => $response->httpCode,
            'Curl Error' => $response->curlError,
            'Curl Errno' => $response->curlErrno,
            'hppUrl' => $order?->hppUrl,
            'status' => $order?->status,
            'cvv2AuthStatus' => $order?->cvv2AuthStatus,
        ]);

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

        $this->logService->log("Payment/{$driver}/GetSimpleStatus", [
            'OrderId' => $order?->id,
            'httpCode' => $response->httpCode,
            'Curl Error' => $response->curlError,
            'Curl Errno' => $response->curlErrno,
            'status' => $order?->status,
        ]);

        return $response;
    }
}
