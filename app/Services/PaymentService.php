<?php

namespace App\Services;

use App\Contracts\ILogger;
use App\DataTransferObjects\Payment\Order\SimpleStatus\SimpleStatusResponseDto;
use App\Repositories\Payment\PaymentRepository;

class PaymentService
{
    /**
     * @param ILogger $logService
     * @param PaymentRepository $paymentRepository
     */
    public function __construct(
        private readonly ILogger           $logService,
        private readonly PaymentRepository $paymentRepository,
    )
    {
    }

    /**
     * @param float $amount
     * @param string $description
     * @return string|null
     */
    public function createOrder(float $amount, string $description): ?string
    {
        $response = $this->paymentRepository->createOrder($amount, $description);
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
     * @param int $orderId
     * @return SimpleStatusResponseDto
     */
    public function getSimpleStatusByOrderId(int $orderId): SimpleStatusResponseDto
    {
        $response = $this->paymentRepository->getSimpleStatusByOrderId($orderId);
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
