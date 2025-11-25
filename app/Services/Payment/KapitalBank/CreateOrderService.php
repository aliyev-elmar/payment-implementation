<?php

namespace App\Services\Payment\KapitalBank;

use App\DataTransferObjects\CurlResponseDto;
use App\DataTransferObjects\Payment\{CreateOrderDto, SimpleStatusDto};
use App\Services\CurlService;
use App\Services\Payment\IOrderPayment;
use App\Repositories\Payment\KapitalBankRepository;
use App\Traits\LogTrait;
use App\Exceptions\Payment\CreateOrderException;

class CreateOrderService implements IOrderPayment
{

    use LogTrait;

    /**
     * @param CurlService $curlService
     * @param KapitalBankRepository $kapitalBankRepository
     */
    public function __construct(
        private readonly CurlService $curlService,
        private readonly KapitalBankRepository $kapitalBankRepository,
    )
    {
    }

    /**
     * Send Request To Create Order By typeRid
     *
     * @param string $typeRid
     * @param float $amount
     * @param string $description
     * @param string $hppRedirectUrl
     * @param string $logPath
     * @return CreateOrderDto
     */
    public function sendRequest(string $typeRid, float $amount, string $description, string  $hppRedirectUrl, string $logPath): CreateOrderDto
    {
        $apiResponse = $this->curlService->postRequest(
            $this->kapitalBankRepository->apiUrl,
            $this->makeRequestBody($typeRid, $amount, $description, $hppRedirectUrl),
            $this->kapitalBankRepository->getRequestHeader()
        );

        $this->logRequest($apiResponse, $logPath);

        $order = $apiResponse->response?->order;
        if(is_null($order)) throw new CreateOrderException($typeRid, $apiResponse->httpCode);

        return new CreateOrderDto($apiResponse->httpCode, $order);
    }

    /**
     * Get Form Url By Order Object
     *
     * @param object $order
     * @return string
     */
    public function getFormUrlByOrder(object $order): string
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
        return $this->kapitalBankRepository->getSimpleStatusByOrderId($orderId);
    }

    /**
     * @param string $typeRid
     * @param float $amount
     * @param string $description
     * @param string $hppRedirectUrl
     * @return string
     */
    private function makeRequestBody(string $typeRid, float $amount,  string $description, string $hppRedirectUrl): string
    {
        $body = [
            'order' => [
                'typeRid' => $typeRid,
                'amount' => $amount,
                'currency' => $this->kapitalBankRepository->currency,
                'language' => $this->kapitalBankRepository->language,
                'description' => $description,
                'hppRedirectUrl' => $hppRedirectUrl,
                'hppCofCapturePurposes' => $this->kapitalBankRepository->hppCofCapturePurposes
            ]
        ];

        return json_encode($body);
    }

    /**
     * @param CurlResponseDto $apiResponse
     * @param string $logPath
     * @return void
     */
    private function logRequest(CurlResponseDto $apiResponse, string $logPath): void
    {
        $order = $apiResponse->response?->order;

        $logText  = "OrderId : {$order?->id}, ";
        $logText .= "httpCode : {$apiResponse->httpCode}, ";
        $logText .= "Curl Error : {$apiResponse->curlError}, ";
        $logText .= "Curl Errno : {$apiResponse->curlErrno}, ";
        $logText .= "hppUrl : {$order?->hppUrl}, ";
//        $logText .= "password : {$order?->password}, ";
        $logText .= "status : {$order?->status}, ";
        $logText .= "cvv2AuthStatus : {$order?->cvv2AuthStatus}, ";
        $logText .= "secret : {$order?->secret}, ";

        $this->writeLogs($logPath, $logText);
    }
}
