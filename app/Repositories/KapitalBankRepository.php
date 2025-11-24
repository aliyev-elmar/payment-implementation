<?php

namespace App\Repositories;

use App\DTOs\Payment\{SimpleStatusDto, DetailedStatusDto};
use App\Services\CurlService;
use Illuminate\Http\Response;
use App\Exceptions\PaymentOrderNotFound;

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
    public string $currency = 'AZN';

    /**
     * @var string
     */
    public string $language = 'az';

    /**
     * @var array
     */
    public array $hppCofCapturePurposes = ['Cit'];

    /**
     * Set API URL && Authorization Basic
     */
    public function __construct(private readonly CurlService $curlService)
    {
        $this->apiUrl = config('payment_systems.kapitalbank.prod_api');
    }

    /**
     * @param int $orderId
     * @return SimpleStatusDto
     */
    public function getSimpleStatusByOrderId(int $orderId): SimpleStatusDto
    {
        $curlUrl = $this->apiUrl . $orderId;
        $apiResponse = $this->curlService->getRequest($curlUrl, $this->getRequestHeader());
        $order = $apiResponse->response?->order;

        if(is_null($order)) {
            throw new PaymentOrderNotFound('Get Order Simple Status Prosesi zamanı xəta baş verdi', $apiResponse->httpCode);
        }

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
     * @return array
     */
    private function getRequestHeader(): array
    {
        $authorization = config('payment_systems.kapitalbank.prod_user') . ':' . config('payment_systems.kapitalbank.prod_pass');
        $token = base64_encode($authorization);

        return [
            "Accept: {$this->contentType}",
            "Content-Type: {$this->contentType}",
            "Authorization: Basic {$token}"
        ];
    }
}
