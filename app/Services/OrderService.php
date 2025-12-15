<?php

namespace App\Services;

use App\Enums\Payment\Order\OrderTypeRid;
use App\DataTransferObjects\Payment\Order\SimpleStatus\SimpleStatusResponseDto;
use App\DataTransferObjects\Payment\Order\SetSourceToken\SetSourceTokenResponseDto;
use App\Repositories\{
    OrderRepository,
    OrderSourceTokenRepository,
    OrderSourceTokenCardRepository,
};
use Illuminate\Support\Facades\Crypt;
use App\Exceptions\OrderNotFoundException;

class OrderService
{
    /**
     * @param PaymentService $paymentService
     * @param OrderRepository $orderRepository
     * @param OrderSourceTokenRepository $orderSourceTokenRepository
     * @param OrderSourceTokenCardRepository $orderSourceTokenCardRepository
     */
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly OrderRepository $orderRepository,
        private readonly OrderSourceTokenRepository $orderSourceTokenRepository,
        private readonly OrderSourceTokenCardRepository $orderSourceTokenCardRepository,
    )
    {
    }

    /**
     * @param string $driver
     * @param OrderTypeRid $orderTypeRid
     * @param int $amount
     * @param string $description
     * @return string|null
     */
    public function create(string $driver, OrderTypeRid $orderTypeRid, int $amount, string $description): ?string
    {
        $response = $this->paymentService->createOrder($driver, $orderTypeRid, $amount, $description);
        $this->orderRepository->create($response->order);

        return $response->formUrl;
    }

    /**
     * @param string $driver
     * @param int $orderId
     * @return SetSourceTokenResponseDto
     */
    public function setSourceToken(string $driver, int $orderId) : SetSourceTokenResponseDto
    {
        $order = $this->orderRepository->getByExternalId($orderId);
        if(!$order) throw new OrderNotFoundException();

        $response = $this->paymentService->setSourceToken($driver, $orderId, Crypt::decryptString($order->password));

        $srcToken = $response->order->srcToken;
        $this->orderSourceTokenRepository->create($orderId, $srcToken);
        $this->orderSourceTokenCardRepository->create($srcToken->id, $srcToken->card);

        return $response;
    }

    /**
     * @param string $driver
     * @param int $orderId
     * @return SimpleStatusResponseDto
     */
    public function getSimpleStatusByOrderId(string $driver, int $orderId): SimpleStatusResponseDto
    {
        $response = $this->paymentService->getSimpleStatusByOrderId($driver, $orderId);

        $order = $this->orderRepository->getByExternalId($orderId);
        $this->orderRepository->updateStatus($order, $response->order->status);

        return $response;
    }
}
