<?php

namespace App\Services\Payment;

use App\Contracts\ICreateOrderService;
use App\Contracts\IPaymentRepository;
use App\Enums\Payment\OrderStatus;

class OrderService
{
    /**
     * @param ICreateOrderService $createOrderService
     * @param IPaymentRepository $paymentRepository
     */
    public function __construct(
        private readonly ICreateOrderService $createOrderService,
        private readonly IPaymentRepository  $paymentRepository,
    )
    {
    }

    /**
     * @param float $amount
     * @param string|null $description
     * @return string
     */
    public function create(float $amount, ?string $description = 'description'): string
    {
        $response = $this->createOrderService->create(
            $this->paymentRepository->getTypeRid(),
            $amount,
            $description,
            $this->paymentRepository->getHppRedirectUrl(),
            $this->paymentRepository->getLogPath('Purchase'),
        );

        $order = $response->order;
        return $this->createOrderService->getFormUrlByOrder($order);
    }

    /**
     * @param int $orderId
     * @return bool
     * @throws \Exception
     */
    public function checkStatusById(int $orderId): bool
    {
        $simpleStatus = $this->createOrderService->getSimpleStatusByOrderId($orderId);
        $order = $simpleStatus->order;

        if ($order->status !== OrderStatus::FULLY_PAID->value) {
            return false;
        }
        return true;
    }
}
