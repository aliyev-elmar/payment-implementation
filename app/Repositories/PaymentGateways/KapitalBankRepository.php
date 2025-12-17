<?php

namespace App\Repositories\PaymentGateways;

use Illuminate\Http\Response;
use App\Enums\Payment\{Currency, Language, ErrorCode};
use App\Enums\Payment\Order\{InitiationEnvKind, OrderTypeRid};
use App\Services\{CurlService, LogService};
use App\DataTransferObjects\Payment\Order\SetSourceToken\{
    SetSourceTokenDto,
    SourceTokenDto,
    SourceTokenCardDto,
    SetSourceTokenResponseDto,
};
use App\DataTransferObjects\Payment\Order\{
    CreateOrderDto,
    CreateOrderResponseDto,
};
use App\DataTransferObjects\Payment\Order\SimpleStatus\{
    SimpleStatusDto,
    SimpleStatusResponseDto,
    SimpleStatusType,
};
use App\Exceptions\{
    InvalidOrderStateException,
    InvalidRequestException,
    InvalidTokenException,
    OrderNotFoundException,
};

class KapitalBankRepository extends PaymentGatewayRepository
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
    private readonly string $currency;

    /**
     * @var string
     */
    private readonly string $language;

    /**
     * @var array
     */
    private array $hppCofCapturePurposes = ['Cit'];

    /**
     * Set API URL && Authorization Basic
     */
    public function __construct(
        private readonly CurlService $curlService,
        private readonly LogService $logService,
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

        $this->currency = Currency::MANAT->value;
        $this->language = Language::AZERBAIJANI->value;
    }

    /**
     * @param OrderTypeRid $orderTypeRid
     * @param int $amount
     * @param string $description
     * @return CreateOrderResponseDto
     * @throws InvalidRequestException
     */
    public function createOrder(OrderTypeRid $orderTypeRid, int $amount, string $description): CreateOrderResponseDto
    {
        $curlResponseDto = $this->curlService->postRequest(
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
                    'hppCofCapturePurposes' => $this->hppCofCapturePurposes
                ]
            ]),
        );

        $response = $curlResponseDto->response;
        $order = $response?->order;

        $errorCode = object_get($response, 'errorCode',  '');
        $errorDescription = object_get($response, 'errorDescription', '');

        $this->logService->log("Payment/KapitalBank/CreateOrder/{$orderTypeRid->value}", [
            'response' => json_encode($response),
            'httpCode' => $curlResponseDto->httpCode,
            'curlError' => $curlResponseDto->curlError,
            'curlErrno' => $curlResponseDto->curlErrno,
        ]);

        if($curlResponseDto->httpCode === Response::HTTP_BAD_REQUEST && $errorCode === ErrorCode::INVALID_REQUEST->value) {
            throw new InvalidRequestException($errorDescription);
        }

        $order = new CreateOrderDto(
            id: object_get($order, 'id'),
            hppUrl: object_get($order, 'hppUrl'),
            password: object_get($order, 'password'),
            status: object_get($order, 'status'),
            cvv2AuthStatus: object_get($order, 'cvv2AuthStatus'),
            secret: object_get($order, 'secret'),
        );

        return new CreateOrderResponseDto(
            httpCode: $curlResponseDto->httpCode,
            order: $order,
            curlError: $curlResponseDto->curlError,
            curlErrno: $curlResponseDto->curlErrno,
            formUrl: "{$order->hppUrl}?id={$order->id}&password={$order->password}",
        );
    }

    /**
     * @param int $orderId
     * @param string $orderPassword
     * @return SetSourceTokenResponseDto
     * @throws InvalidTokenException
     * @throws InvalidRequestException
     * @throws InvalidOrderStateException
     */
    public function setSourceToken(int $orderId, string $orderPassword): SetSourceTokenResponseDto
    {
        $curlResponseDto = $this->curlService->postRequest(
            $this->apiUrl . $orderId . "/set-src-token?password=$orderPassword",
            $this->getHeader(),
            json_encode([
                'order' => [
                    'initiationEnvKind' => InitiationEnvKind::MIT->value,
                ],
                'token' => [
                    'storedId' => $orderId
                ]
            ]),
        );

        $response = $curlResponseDto->response;
        $order = object_get($response, 'order');
        $srcToken = object_get($response, 'srcToken');
        $card = object_get($srcToken, 'card');

        $errorCode = object_get($response, 'errorCode', '');
        $errorDescription = object_get($response, 'errorDescription', '');

        $this->logService->log("Payment/KapitalBank/SetSourceToken", [
            'response' => json_encode($response),
            'httpCode' => $curlResponseDto->httpCode,
            'curlError' => $curlResponseDto->curlError,
            'curlErrno' => $curlResponseDto->curlErrno,
        ]);

        if($curlResponseDto->httpCode === Response::HTTP_BAD_REQUEST && $errorCode === ErrorCode::INVALID_TOKEN->value) {
            throw new InvalidTokenException($errorDescription);
        }

        if($curlResponseDto->httpCode === Response::HTTP_BAD_REQUEST && $errorCode === ErrorCode::INVALID_REQUEST->value) {
            throw new InvalidRequestException($errorDescription);
        }

        if($curlResponseDto->httpCode === Response::HTTP_BAD_REQUEST && $errorCode === ErrorCode::INVALID_ORDER_STATE->value) {
            throw new InvalidOrderStateException($errorDescription);
        }

        $card = new SourceTokenCardDto(
            expiration: object_get($card, 'expiration'),
            brand: object_get($card, 'brand'),
        );

        $srcToken = new SourceTokenDto(
            id: object_get($srcToken, 'id'),
            paymentMethod: object_get($srcToken, 'paymentMethod'),
            role: object_get($srcToken, 'role'),
            status: object_get($srcToken, 'status'),
            regTime: object_get($srcToken, 'regTime'),
            displayName: object_get($srcToken, 'displayName'),
            card: $card,
        );

        $order = new SetSourceTokenDto(
            status: object_get($order, 'status'),
            cvv2AuthStatus: object_get($order, 'cvv2AuthStatus'),
            tdsV1AuthStatus: object_get($order, 'tdsV1AuthStatus'),
            tdsV2AuthStatus: object_get($order, 'tdsV2AuthStatus'),
            otpAutStatus: object_get($order, 'otpAutStatus'),
            srcToken: $srcToken,
        );

        return new SetSourceTokenResponseDto(
            httpCode: $curlResponseDto->httpCode,
            order: $order,
            curlError: $curlResponseDto->curlError,
            curlErrno: $curlResponseDto->curlErrno,
        );
    }

    /**
     * @param int $orderId
     * @return SimpleStatusResponseDto
     * @throws InvalidRequestException
     * @throws OrderNotFoundException
     */
    public function getSimpleStatusByOrderId(int $orderId): SimpleStatusResponseDto
    {
        $curlResponseDto = $this->curlService->getRequest($this->apiUrl . $orderId, $this->getHeader());
        $response = $curlResponseDto->response;
        $order = $response?->order;

        $errorCode = object_get($response, 'errorCode', '');
        $errorDescription = object_get($response, 'errorDescription', '');

        $this->logService->log("Payment/KapitalBank/GetSimpleStatus", [
            'response' => json_encode($response),
            'httpCode' => $curlResponseDto->httpCode,
            'curlError' => $curlResponseDto->curlError,
            'curlErrno' => $curlResponseDto->curlErrno,
        ]);

        if($curlResponseDto->httpCode === Response::HTTP_BAD_REQUEST && $errorCode === ErrorCode::INVALID_REQUEST->value) {
            throw new InvalidRequestException($errorDescription);
        }

        if(is_null($order)) {
            throw new OrderNotFoundException();
        }

        $orderType = object_get($order, 'type');
        $orderTypeTitle = object_get($orderType, 'title');

        $simpleStatusType = new SimpleStatusType(
            title: $orderTypeTitle,
        );

        $simpleStatus = new SimpleStatusDto(
            id: object_get($order, 'id'),
            typeRid: object_get($order, 'typeRid'),
            status: object_get($order, 'status'),
            prevStatus: object_get($order, 'prevStatus'),
            lastStatusLogin: object_get($order, 'lastStatusLogin'),
            amount: object_get($order, 'amount'),
            currency: object_get($order, 'currency'),
            createTime: object_get($order, 'createTime'),
            finishTime: object_get($order, 'finishTime'),
            type: $simpleStatusType,
        );

        return new SimpleStatusResponseDto(
            httpCode: $curlResponseDto->httpCode,
            order: $simpleStatus,
            curlError: $curlResponseDto->curlError,
            curlErrno: $curlResponseDto->curlErrno,
        );
    }

    /**
     * @return array
     */
    private function getHeader(): array
    {
        $contentType = 'application/json';
        $token = base64_encode($this->user . ':' . $this->pass);

        return [
            "Accept: {$contentType}",
            "Content-Type: {$contentType}",
            "Authorization: Basic {$token}"
        ];
    }
}
