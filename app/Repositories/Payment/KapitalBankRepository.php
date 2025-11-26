<?php

namespace App\Repositories\Payment;

use App\Contracts\IPaymentRepository;
use App\DataTransferObjects\Payment\Order\{DetailedStatusDto, OrderDto, SimpleStatusDto};
use App\Services\CurlService;
use Illuminate\Http\Response;
use App\Exceptions\Payment\GetOrderStatusException;

class KapitalBankRepository implements IPaymentRepository
{
    /**
     * @var string
     */
    public string $apiUrl;

    /**
     * @var string
     */
    public string $contentType = 'application/json';

    /**
     * @var string
     */
    private string $confFile = 'payment_systems.kapitalbank';

    /**
     * Set API URL && Authorization Basic
     */
    public function __construct(private readonly CurlService $curlService)
    {
        $this->apiUrl = config("{$this->confFile}.test_api");
    }

    /**
     * @return array
     */
    public function getRequestHeader(): array
    {
        $authorization = config("{$this->confFile}.test_user") . ':' . config("{$this->confFile}.test_pass");
        $token = base64_encode($authorization);

        return [
            "Accept: {$this->contentType}",
            "Content-Type: {$this->contentType}",
            "Authorization: Basic {$token}"
        ];
    }

    /**
     * @param int $amount
     * @param string $description
     * @return array
     */
    public function getCreateOrderRequestBody(int $amount, string $description): array
    {
        return [
            'order' => [
                'typeRid' => config("{$this->confFile}.order.typeRid.Purchase"),
                'amount' => $amount,
                'currency' => '944',
                'language' => 'AZ',
                'description' => $description,
                'hppRedirectUrl' => config("{$this->confFile}.test_hpp_redirect_url"),
                'hppCofCapturePurposes' => ['Cit'],
            ]
        ];
    }

    /**
     * @param int $orderId
     * @return SimpleStatusDto
     * @throws GetOrderStatusException
     */
    public function getSimpleStatusByOrderId(int $orderId): SimpleStatusDto
    {
        $curlUrl = $this->apiUrl . $orderId;
        $apiResponse = $this->curlService->getRequest($curlUrl, $this->getRequestHeader());
        $order = $apiResponse->response?->order;

        if(is_null($order)) {
            throw new GetOrderStatusException($apiResponse->httpCode);
        }

        $order = new OrderDto(
            id: $order->id,
            hppUrl: $order->hpp_url,
            password: $order->password,
            status: $order->status,
            cvv2AuthStatus: $order->cvv2AuthStatus,
            secret: $order->secret,
        );

        return new SimpleStatusDto(
            httpCode: $apiResponse->httpCode,
            order: $order,
        );
    }

    /**
     * @param int $orderId
     * @return DetailedStatusDto
     */
    public function getDetailedStatusByOrderId(int $orderId): DetailedStatusDto
    {
        return new DetailedStatusDto(
            httpCode: Response::HTTP_NOT_FOUND,
        );
    }

    /**
     * @param string $subFolderPath
     * @return string
     */
    public function getLogPath(string $subFolderPath): string
    {
        return "Payment/KapitalBank/{$subFolderPath}";
    }
}
