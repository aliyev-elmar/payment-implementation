<?php

namespace App\Repositories\PaymentGateways;

use App\Contracts\IPaymentGateway;
use App\Enums\Payment\Currency;
use App\Enums\Payment\Language;
use App\Enums\Payment\Order\OrderTypeRid;
use App\Traits\Logger;
use App\Services\CurlService;
use Illuminate\Http\Response;
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
use App\Exceptions\{InvalidOrderStateException, InvalidRequestException, OrderNotFoundException};

class KapitalBankRepository implements IPaymentGateway
{
    use Logger;

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

        $this->log("Payment/KapitalBank/CreateOrder/{$orderTypeRid->value}", [
            'response' => json_encode($response),
            'httpCode' => $curlResponseDto->httpCode,
            'curlError' => $curlResponseDto->curlError,
            'curlErrno' => $curlResponseDto->curlErrno,
        ]);

        if($curlResponseDto->httpCode === Response::HTTP_BAD_REQUEST) {
            throw new InvalidRequestException(self::getPropertyValueByObject($response, 'errorDescription') ?? '');
        }

        $order = new CreateOrderDto(
            id: self::getPropertyValueByObject($order, 'id'),
            hppUrl: self::getPropertyValueByObject($order, 'hppUrl'),
            password: self::getPropertyValueByObject($order, 'password'),
            status: self::getPropertyValueByObject($order, 'status'),
            cvv2AuthStatus: self::getPropertyValueByObject($order, 'cvv2AuthStatus'),
            secret: self::getPropertyValueByObject($order, 'secret'),
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
     */
    public function setSourceToken(int $orderId, string $orderPassword): SetSourceTokenResponseDto
    {
        $curlResponseDto = $this->curlService->postRequest(
            $this->apiUrl . $orderId . "/set-src-token?password=$orderPassword",
            $this->getHeader(),
            json_encode([
                'order' => [
                    'initiationEnvKind' => 'Server'
                ],
                'token' => [
                    'storedId' => $orderId
                ]
            ]),
        );

        $response = $curlResponseDto->response;
        $order = self::getPropertyValueByObject($response, 'order');
        $srcToken = self::getPropertyValueByObject($response, 'srcToken');
        $card = self::getPropertyValueByObject($srcToken, 'card');

        $errorCode = self::getPropertyValueByObject($response, 'errorCode') ?? '';
        $errorDescription = self::getPropertyValueByObject($response, 'errorDescription') ?? '';

        $this->log("Payment/KapitalBank/SetSourceToken", [
            'response' => json_encode($response),
            'httpCode' => $curlResponseDto->httpCode,
            'curlError' => $curlResponseDto->curlError,
            'curlErrno' => $curlResponseDto->curlErrno,
        ]);

        if($curlResponseDto->httpCode === Response::HTTP_BAD_REQUEST && $errorCode === 'InvalidToken') {
            throw new InvalidRequestException($errorDescription);
        }

        if($curlResponseDto->httpCode === Response::HTTP_BAD_REQUEST && $errorCode === 'InvalidRequest') {
            throw new InvalidRequestException($errorDescription);
        }

        if($curlResponseDto->httpCode === Response::HTTP_BAD_REQUEST && $errorCode === 'InvalidOrderState') {
            throw new InvalidOrderStateException($errorDescription);
        }

        $card = new SourceTokenCardDto(
            expiration: self::getPropertyValueByObject($card, 'expiration'),
            brand: self::getPropertyValueByObject($card, 'brand'),
        );

        $srcToken = new SourceTokenDto(
            id: self::getPropertyValueByObject($srcToken, 'id'),
            paymentMethod: self::getPropertyValueByObject($srcToken, 'paymentMethod'),
            role: self::getPropertyValueByObject($srcToken, 'role'),
            status: self::getPropertyValueByObject($srcToken, 'status'),
            regTime: self::getPropertyValueByObject($srcToken, 'regTime'),
            displayName: self::getPropertyValueByObject($srcToken, 'displayName'),
            card: $card,
        );

        $order = new SetSourceTokenDto(
            status: self::getPropertyValueByObject($order, 'status'),
            cvv2AuthStatus: self::getPropertyValueByObject($order, 'cvv2AuthStatus'),
            tdsV1AuthStatus: self::getPropertyValueByObject($order, 'tdsV1AuthStatus'),
            tdsV2AuthStatus: self::getPropertyValueByObject($order, 'tdsV2AuthStatus'),
            otpAutStatus: self::getPropertyValueByObject($order, 'otpAutStatus'),
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

        $this->log("Payment/KapitalBank/GetSimpleStatus", [
            'response' => json_encode($response),
            'httpCode' => $curlResponseDto->httpCode,
            'curlError' => $curlResponseDto->curlError,
            'curlErrno' => $curlResponseDto->curlErrno,
        ]);

        if($curlResponseDto->httpCode === Response::HTTP_BAD_REQUEST) {
            throw new InvalidRequestException(self::getPropertyValueByObject($response, 'errorDescription') ?? '');
        }

        if(is_null($order)) {
            throw new OrderNotFoundException();
        }

        $orderType = self::getPropertyValueByObject($order, 'type');
        $orderTypeTitle = self::getPropertyValueByObject($orderType, 'title');

        $simpleStatusType = new SimpleStatusType(
            title: $orderTypeTitle,
        );

        $simpleStatus = new SimpleStatusDto(
            id: self::getPropertyValueByObject($order, 'id'),
            typeRid: self::getPropertyValueByObject($order, 'typeRid'),
            status: self::getPropertyValueByObject($order, 'status'),
            prevStatus: self::getPropertyValueByObject($order, 'prevStatus'),
            lastStatusLogin: self::getPropertyValueByObject($order, 'lastStatusLogin'),
            amount: self::getPropertyValueByObject($order, 'amount'),
            currency: self::getPropertyValueByObject($order, 'currency'),
            createTime: self::getPropertyValueByObject($order, 'createTime'),
            finishTime: self::getPropertyValueByObject($order, 'finishTime'),
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

    /**
     * @param object|null $object
     * @param string $property
     * @return mixed
     */
    private static function getPropertyValueByObject(?object $object, string $property): mixed
    {
        if(is_null($object)) return null;
        return property_exists($object, $property) ? $object->{$property} : null;
    }
}
