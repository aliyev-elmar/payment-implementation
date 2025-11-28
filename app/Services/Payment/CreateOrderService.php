<?php

namespace App\Services\Payment;

use App\Enums\Payment\OrderStatus;
use App\Contracts\ILogger;
use App\DataTransferObjects\Payment\Order\{CreateDto, OrderDto};
use App\Services\CurlService;
use App\Repositories\Payment\PaymentRepository;
use App\Exceptions\Payment\{CreateOrderException, GetOrderStatusException};

class CreateOrderService
{
    /**
     * @param CurlService $curlService
     * @param ILogger $logService
     * @param PaymentRepository $paymentRepository
     */
    public function __construct(
        private readonly CurlService       $curlService,
        private readonly ILogger           $logService,
        private readonly PaymentRepository $paymentRepository,
    )
    {
    }

    /**
     * @param float $amount
     * @param string $description
     * @return CreateDto
     * @throws CreateOrderException
     */
    public function create(float $amount, string $description): CreateDto
    {
        $body = $this->paymentRepository->getCreateOrderRequestBody($amount, $description);
        $body = json_encode($body);

        $apiResponse = $this->curlService->postRequest(
            $this->paymentRepository->apiUrl,
            $body,
            $this->paymentRepository->getRequestHeader(),
        );

        $response = $apiResponse->response;
        $order = $response?->order;

        $logPath = $this->paymentRepository->getLogPath('Purchase');

        $logText  = "OrderId : {$order?->id}, ";
        $logText .= "httpCode : {$apiResponse->httpCode}, ";
        $logText .= "Curl Error : {$apiResponse->curlError}, ";
        $logText .= "Curl Errno : {$apiResponse->curlErrno}, ";
        $logText .= "hppUrl : {$order?->hppUrl}, ";
        $logText .= "status : {$order?->status}, ";
        $logText .= "cvv2AuthStatus : {$order?->cvv2AuthStatus}, ";
        $this->logService->log($logPath, $logText);

        if(is_null($order)) throw new CreateOrderException($response->httpCode);

        return new CreateDto($response->httpCode, $order);
    }

    /**
     * @param int $orderId
     * @return bool
     * @throws GetOrderStatusException
     */
    public function checkSimpleStatusById(int $orderId): bool
    {
        $simpleStatus = $this->paymentRepository->getSimpleStatusByOrderId($orderId);
        $order = $simpleStatus->order;

        return $order->status === OrderStatus::FULLY_PAID->value;
    }

    /**
     * Get Form Url By Order Object
     *
     * @param OrderDto $order
     * @return string
     */
    public function getFormUrlByOrder(OrderDto $order): string
    {
        return "{$order->hppUrl}?id={$order->id}&password={$order->password}";
    }
}
