<?php

namespace App\Repositories\PaymentGateways;

use App\Contracts\IPaymentGateway;
use App\Traits\InteractsWithObjects;
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

class KapitalBankRepository implements IPaymentGateway
{
    use InteractsWithObjects;

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

        $errorCode = static::getPropertyValueByObject($response, 'errorCode') ?? '';
        $errorDescription = static::getPropertyValueByObject($response, 'errorDescription') ?? '';

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
            id: static::getPropertyValueByObject($order, 'id'),
            hppUrl: static::getPropertyValueByObject($order, 'hppUrl'),
            password: static::getPropertyValueByObject($order, 'password'),
            status: static::getPropertyValueByObject($order, 'status'),
            cvv2AuthStatus: static::getPropertyValueByObject($order, 'cvv2AuthStatus'),
            secret: static::getPropertyValueByObject($order, 'secret'),
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
        $order = static::getPropertyValueByObject($response, 'order');
        $srcToken = static::getPropertyValueByObject($response, 'srcToken');
        $card = static::getPropertyValueByObject($srcToken, 'card');

        $errorCode = static::getPropertyValueByObject($response, 'errorCode') ?? '';
        $errorDescription = static::getPropertyValueByObject($response, 'errorDescription') ?? '';

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
            expiration: static::getPropertyValueByObject($card, 'expiration'),
            brand: static::getPropertyValueByObject($card, 'brand'),
        );

        $srcToken = new SourceTokenDto(
            id: static::getPropertyValueByObject($srcToken, 'id'),
            paymentMethod: static::getPropertyValueByObject($srcToken, 'paymentMethod'),
            role: static::getPropertyValueByObject($srcToken, 'role'),
            status: static::getPropertyValueByObject($srcToken, 'status'),
            regTime: static::getPropertyValueByObject($srcToken, 'regTime'),
            displayName: static::getPropertyValueByObject($srcToken, 'displayName'),
            card: $card,
        );

        $order = new SetSourceTokenDto(
            status: static::getPropertyValueByObject($order, 'status'),
            cvv2AuthStatus: static::getPropertyValueByObject($order, 'cvv2AuthStatus'),
            tdsV1AuthStatus: static::getPropertyValueByObject($order, 'tdsV1AuthStatus'),
            tdsV2AuthStatus: static::getPropertyValueByObject($order, 'tdsV2AuthStatus'),
            otpAutStatus: static::getPropertyValueByObject($order, 'otpAutStatus'),
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

        $errorCode = static::getPropertyValueByObject($response, 'errorCode') ?? '';
        $errorDescription = static::getPropertyValueByObject($response, 'errorDescription') ?? '';

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

        $orderType = static::getPropertyValueByObject($order, 'type');
        $orderTypeTitle = static::getPropertyValueByObject($orderType, 'title');

        $simpleStatusType = new SimpleStatusType(
            title: $orderTypeTitle,
        );

        $simpleStatus = new SimpleStatusDto(
            id: static::getPropertyValueByObject($order, 'id'),
            typeRid: static::getPropertyValueByObject($order, 'typeRid'),
            status: static::getPropertyValueByObject($order, 'status'),
            prevStatus: static::getPropertyValueByObject($order, 'prevStatus'),
            lastStatusLogin: static::getPropertyValueByObject($order, 'lastStatusLogin'),
            amount: static::getPropertyValueByObject($order, 'amount'),
            currency: static::getPropertyValueByObject($order, 'currency'),
            createTime: static::getPropertyValueByObject($order, 'createTime'),
            finishTime: static::getPropertyValueByObject($order, 'finishTime'),
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
