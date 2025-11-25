<?php

namespace App\Repositories\Payment;

use App\DataTransferObjects\Payment\{DetailedStatusDto, SimpleStatusDto};
use App\Services\CurlService;
use Illuminate\Http\Response;

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
     * @var string
     */
    private string $confFile = 'payment_systems.kapitalbank';

    /**
     * Set API URL && Authorization Basic
     */
    public function __construct(private readonly CurlService $curlService)
    {
        $this->apiUrl = config("{$this->confFile}.prod_api");
    }

    /**
     * @return array
     */
    public function getRequestHeader(): array
    {
        $authorization = config("{$this->confFile}.prod_user") . ':' . config("{$this->confFile}.prod_pass");
        $token = base64_encode($authorization);

        return [
            "Accept: {$this->contentType}",
            "Content-Type: {$this->contentType}",
            "Authorization: Basic {$token}"
        ];
    }

    /**
     * @param int $orderId
     * @return SimpleStatusDto
     * @throws \Exception
     */
    public function getSimpleStatusByOrderId(int $orderId): SimpleStatusDto
    {
        $curlUrl = $this->apiUrl . $orderId;
        $apiResponse = $this->curlService->getRequest($curlUrl, $this->getRequestHeader());
        $order = $apiResponse->response?->order;

        if(is_null($order)) {
            throw new \Exception('Get Order Simple Status Prosesi zamanı xəta baş verdi', $apiResponse->httpCode);
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
     * @return string
     */
    public function getTypeRid(): string
    {
        return config("{$this->confFile}.order.typeRid.Purchase");
    }

    /**
     * @return string
     */
    public function getHppRedirectUrl(): string
    {
        return config("{$this->confFile}.prod_hpp_redirect_url");
    }

    public function getCurrency(): int
    {
        return '944';
    }

    public function getLanguage(): string
    {
        return 'AZ';
    }

    /**
     * @param string $subFolderPath
     * @return string
     */
    public function getLogPath(string $subFolderPath): string
    {
        return 'Payment/KapitalBank/';
    }
}
