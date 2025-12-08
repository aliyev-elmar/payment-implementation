<?php

namespace App\Services;

use App\Contracts\ILogger;
use App\Enums\Payment\Order\OrderTypeRid;
use App\DataTransferObjects\Payment\Order\CreateOrderResponseDto;
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
        $this->logCreateOrderResponse($driver, $response, $orderTypeRid);

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
        $this->logSimpleStatusResponse($driver, $response);

        return $response;
    }

    /**
     * @param string $driver
     * @param CreateOrderResponseDto $response
     * @param OrderTypeRid $orderTypeRid
     * @return void
     */
    private function logCreateOrderResponse(string $driver, CreateOrderResponseDto $response, OrderTypeRid $orderTypeRid): void
    {
        $order = $response->order;
        $logFolder = "Payment/{$driver}/CreateOrder/{$orderTypeRid->value}";

        $context = [
            'OrderId' => $order?->id,
            'httpCode' => $response->httpCode,
            'Curl Error' => $response->curlError,
            'Curl Errno' => $response->curlErrno,
            'hppUrl' => $order?->hppUrl,
            'status' => $order?->status,
            'cvv2AuthStatus' => $order?->cvv2AuthStatus,
        ];

        $this->logService->log($logFolder, $context);
    }

    /**
     * @param string $driver
     * @param SimpleStatusResponseDto $response
     * @return void
     */
    private function logSimpleStatusResponse(string $driver, SimpleStatusResponseDto $response): void
    {
        $order = $response->order;
        $logFolder = "Payment/{$driver}/GetSimpleStatus";

        $context = [
            'OrderId' => $order?->id,
            'httpCode' => $response->httpCode,
            'Curl Error' => $response->curlError,
            'Curl Errno' => $response->curlErrno,
            'status' => $order?->status,
        ];

        $this->logService->log($logFolder, $context);
    }
}
