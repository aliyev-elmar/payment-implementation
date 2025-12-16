<?php

namespace App\Services;

use App\Enums\Payment\Order\OrderTypeRid;
use App\DataTransferObjects\Payment\Order\SimpleStatus\SimpleStatusResponseDto;
use App\DataTransferObjects\Payment\Order\SetSourceToken\SetSourceTokenResponseDto;
use Illuminate\Support\Facades\{DB, Crypt};
use App\Repositories\{
    OrderRepository,
    OrderSourceTokenRepository,
    OrderSourceTokenCardRepository,
};
use App\Exceptions\{
    InvalidRequestException,
    InvalidTokenException,
    InvalidOrderStateException,
    OrderNotFoundException,
};
use Exception;

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
     * @throws InvalidRequestException
     * @throws Exception
     */
    public function create(string $driver, OrderTypeRid $orderTypeRid, int $amount, string $description): ?string
    {
        DB::beginTransaction();
        try {
            $response = $this->paymentService->createOrder($driver, $orderTypeRid, $amount, $description);
            $this->orderRepository->create($response->order);

            DB::commit();
            return $response->formUrl;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param string $driver
     * @param int $orderId
     * @return SetSourceTokenResponseDto
     * @throws InvalidRequestException
     * @throws InvalidTokenException
     * @throws InvalidOrderStateException
     * @throws Exception
     */
    public function setSourceToken(string $driver, int $orderId): SetSourceTokenResponseDto
    {
        DB::beginTransaction();
        try {
            $order = $this->orderRepository->getByExternalId($orderId);
            if(!$order) throw new OrderNotFoundException();

            $response = $this->paymentService->setSourceToken($driver, $orderId, Crypt::decryptString($order->password));
            $srcToken = $response->order->srcToken;

            $orderSourceToken = $this->orderSourceTokenRepository->create($orderId, $srcToken);
            $this->orderSourceTokenCardRepository->create($orderSourceToken->id, $srcToken->card);

            DB::commit();
            return $response;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param string $driver
     * @param int $orderId
     * @return SimpleStatusResponseDto
     * @throws InvalidRequestException
     * @throws OrderNotFoundException
     */
    public function getSimpleStatusByOrderId(string $driver, int $orderId): SimpleStatusResponseDto
    {
        DB::beginTransaction();
        try {
            $response = $this->paymentService->getSimpleStatusByOrderId($driver, $orderId);

            $order = $this->orderRepository->getByExternalId($orderId);
            if(!$order) throw new OrderNotFoundException();

            $this->orderRepository->updateStatus($order, $response->order->status);
            return $response;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
