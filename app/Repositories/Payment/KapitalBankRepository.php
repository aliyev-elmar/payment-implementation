<?php

namespace App\Repositories\Payment;

use App\DataTransferObjects\Payment\Order\{CreateOrderDto, CreateOrderResponseDto};
use App\DataTransferObjects\Payment\Order\SimpleStatus\{SimpleStatusDto, SimpleStatusType, SimpleStatusResponseDto};
use App\Services\CurlService;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Exceptions\Payment\KapitalBankException;

class KapitalBankRepository extends PaymentRepository
{
    /**
     * @var string
     */
    private readonly string $apiUrl;

    /**
     * @var string
     */
    private readonly string $hppRedirectUrl;

    /**
     * @var array
     */
    private readonly array $header;

    /**
     * @var string
     */
    private readonly string $token;

    /**
     * @var string
     */
    private string $contentType = 'application/json';

    /**
     * @var string
     */
    private string $confFile = 'payment_systems.kapitalbank.test'; // prod

    /**
     * Set API URL && Authorization Basic
     */
    public function __construct(private readonly CurlService $curlService)
    {
        // Set Urls
        $this->apiUrl = config("{$this->confFile}.api");
        $this->hppRedirectUrl = config("{$this->confFile}.hpp_redirect_url");

        // Set token for authorization
        $this->token = base64_encode(config("{$this->confFile}.user") . ':' . config("{$this->confFile}.pass"));

        $this->header = [
            "Accept: {$this->contentType}",
            "Content-Type: {$this->contentType}",
            "Authorization: Basic {$this->token}"
        ];
    }

    /**
     * @param int $amount
     * @param string $description
     * @param string $currency
     * @param string $language
     * @param string $typeRid
     * @param array $hppCofCapturePurposes
     * @return CreateOrderResponseDto
     * @throws NotFoundHttpException
     */
    public function createOrder(
        int $amount,
        string $description,
        string $currency = 'AZN',
        string $language = 'az',
        string $typeRid = 'Purchase',
        array $hppCofCapturePurposes = ['Cit'],
    ): CreateOrderResponseDto
    {
        $apiResponse = $this->curlService->postRequest(
            $this->apiUrl,
            $this->header,
            json_encode([
                'order' => [
                    'typeRid' => config("{$this->confFile}.order.typeRid.{$typeRid}"),
                    'amount' => $amount,
                    'currency' => $currency,
                    'language' => $language,
                    'description' => $description,
                    'hppRedirectUrl' => $this->hppRedirectUrl,
                    'hppCofCapturePurposes' => $hppCofCapturePurposes,
                ]
            ]),
        );

        $response = $apiResponse->response;

        if($apiResponse->httpCode != Response::HTTP_OK) {
            throw new KapitalBankException($apiResponse->httpCode, $response?->errorCode, $response?->errorDescription);
        }

        $order = $response?->order;
        $order = new CreateOrderDto(
            id: $order->id,
            hppUrl: $order->hppUrl,
            password: $order->password,
            status: $order->status,
            cvv2AuthStatus: $order->cvv2AuthStatus,
            secret: $order->secret,
        );

        return new CreateOrderResponseDto(
            httpCode: $apiResponse->httpCode,
            order: $order,
            curlError: $apiResponse->curlError,
            curlErrno: $apiResponse->curlErrno,
            logFolderPath: "Payment/KapitalBank/{$typeRid}",
            formUrl: "{$order->hppUrl}?id={$order->id}&password={$order->password}",
        );
    }

    /**
     * @param int $orderId
     * @return SimpleStatusResponseDto
     * @throws NotFoundHttpException
     */
    public function getSimpleStatusByOrderId(int $orderId): SimpleStatusResponseDto
    {
        $apiResponse = $this->curlService->getRequest($this->apiUrl . $orderId, $this->header);
        $response = $apiResponse->response;
        $order = $response?->order;

        if($apiResponse->httpCode != Response::HTTP_OK) {
            throw new KapitalBankException($apiResponse->httpCode, $response?->errorCode, $response?->errorDescription);
        }

        if(is_null($order)) {
            throw new NotFoundHttpException('Order not found');
        }

        $simpleStatusType = new SimpleStatusType(
            title: $order->type->title,
        );

        $simpleStatus = new SimpleStatusDto(
            id: $order->id,
            typeRid: $order->typeRid,
            status: $order->status,
            lastStatusLogin: $order->lastStatusLogin,
            amount: $order->amount,
            currency: $order->currency,
            type: $simpleStatusType,
        );

        return new SimpleStatusResponseDto(
            httpCode: $apiResponse->httpCode,
            order: $simpleStatus,
            curlError: $apiResponse->curlError,
            curlErrno: $apiResponse->curlErrno,
            logFolderPath: "Payment/KapitalBank/GetSimpleStatus",
        );
    }
}
