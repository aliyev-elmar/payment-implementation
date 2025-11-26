<?php

namespace App\Services\Payment\KapitalBank;

use App\Enums\Payment\OrderStatus;
use App\Contracts\{ICreateOrderService, ILogService, IPaymentRepository};
use App\DataTransferObjects\Payment\Order\{CreateDto, OrderDto, SimpleStatusDto};
use App\Exceptions\Payment\CreateOrderException;
use App\Services\CurlService;

class CreateOrderService implements ICreateOrderService
{
    /**
     * @param CurlService $curlService
     * @param ILogService $logService
     * @param IPaymentRepository $paymentRepository
     */
    public function __construct(
        private readonly CurlService $curlService,
        private readonly ILogService $logService,
        private readonly IPaymentRepository $paymentRepository,
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
        $typeRid = $this->paymentRepository->getTypeRid();

        $body = json_encode([
            'order' => [
                'typeRid' => $typeRid,
                'amount' => $amount,
                'currency' => $this->paymentRepository->getCurrency(),
                'language' => $this->paymentRepository->getLanguage(),
                'description' => $description,
                'hppRedirectUrl' => $this->paymentRepository->getHppRedirectUrl(),
                'hppCofCapturePurposes' => $this->paymentRepository->getHppCofCapturePurposes(),
            ]
        ]);

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

        if(is_null($order)) throw new CreateOrderException($typeRid, $response->httpCode);

        return new CreateDto($response->httpCode, $order);
    }

    /**
     * @param int $orderId
     * @return bool
     * @throws \Exception
     */
    public function checkStatusById(int $orderId): bool
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

    /**
     * @param int $orderId
     * @return SimpleStatusDto
     * @throws \Exception
     */
    public function getSimpleStatusByOrderId(int $orderId): SimpleStatusDto
    {
        return $this->paymentRepository->getSimpleStatusByOrderId($orderId);
    }
}
