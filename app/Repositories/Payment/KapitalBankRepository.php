<?php

namespace App\Repositories\Payment;

use App\Contracts\IPaymentGateway;
use App\Enums\Payment\Order\OrderTypeRid;
use App\DataTransferObjects\Payment\Order\{CreateOrderDto, CreateOrderResponseDto};
use App\DataTransferObjects\Payment\Order\SimpleStatus\{SimpleStatusDto, SimpleStatusResponseDto, SimpleStatusType};
use App\Services\CurlService;
use Illuminate\Http\Response;
use App\Exceptions\{PaymentGatewayException, OrderNotFoundException};

class KapitalBankRepository implements IPaymentGateway
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
     * @var string
     */
    private readonly string $user;

    /**
     * @var string
     */
    private readonly string $pass;

    /**
     * @var string
     */
    private string $contentType = 'application/json';

    /**
     * @var string
     */
    private string $currency = 'AZN';

    /**
     * @var string
     */
    private string $language = 'az';

    /**
     * @var array
     */
    private array $hppCofCapturePurposes = ['Cit'];

    /**
     * @var string
     */
    private string $paymentGateway = 'Kapital Bank';

    /**
     * Set API URL && Authorization Basic
     */
    public function __construct(
        private readonly CurlService $curlService,
        string $apiUrl,
        string $hppRedirectUrl,
        string $user,
        string $pass
    )
    {
        $this->apiUrl = $apiUrl;
        $this->hppRedirectUrl = $hppRedirectUrl;
        $this->user = $user;
        $this->pass = $pass;
    }

    /**
     * @param OrderTypeRid $orderTypeRid
     * @param int $amount
     * @param string $description
     * @return CreateOrderResponseDto
     * @throws PaymentGatewayException
     */
    public function createOrder(OrderTypeRid $orderTypeRid, int $amount, string $description): CreateOrderResponseDto
    {
        $apiResponse = $this->curlService->postRequest(
            $this->apiUrl,
            $this->getHeader(),
            json_encode([
                'order' => [
                    'typeRid' => $orderTypeRid->value,
                    'amount' => $amount,
                    'currency' => $this->currency,
                    'language' => $this->language,
                    'description' => $description,
                    'hppRedirectUrl' => $this->hppRedirectUrl,
                    'hppCofCapturePurposes' => $this->hppCofCapturePurposes,
                ]
            ]),
        );

        $response = $apiResponse->response;

        if($apiResponse->httpCode != Response::HTTP_OK) {
            throw new PaymentGatewayException(
                $this->paymentGateway,
                $apiResponse->httpCode,
                $response?->errorCode,
                $response?->errorDescription,
            );
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
            formUrl: "{$order->hppUrl}?id={$order->id}&password={$order->password}",
        );
    }

    /**
     * @param int $orderId
     * @return SimpleStatusResponseDto
     * @throws PaymentGatewayException
     * @throws OrderNotFoundException
     */
    public function getSimpleStatusByOrderId(int $orderId): SimpleStatusResponseDto
    {
        $apiResponse = $this->curlService->getRequest($this->apiUrl . $orderId, $this->getHeader());
        $response = $apiResponse->response;

        if($apiResponse->httpCode != Response::HTTP_OK) {
            throw new PaymentGatewayException(
                $this->paymentGateway,
                $apiResponse->httpCode,
                $response?->errorCode,
                $response?->errorDescription,
            );
        }

        $order = $response?->order;
        if(is_null($order)) {
            throw new OrderNotFoundException($this->paymentGateway);
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
        );
    }

    /**
     * @return array
     */
    private function getHeader(): array
    {
        $token = base64_encode($this->user . ':' . $this->pass);

        return [
            "Accept: {$this->contentType}",
            "Content-Type: {$this->contentType}",
            "Authorization: Basic {$token}"
        ];
    }
}
