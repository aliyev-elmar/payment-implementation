<?php

namespace App\Http\Controllers;

use App\Enums\Payment\Order\OrderTypeRid;
use App\Http\Requests\Order\StoreRequest;
use App\Services\PaymentService;
use Illuminate\Http\{Response, JsonResponse};

class OrderController extends Controller
{
    /**
     * @var string
     */
    private readonly string $paymentDriver;

    /**
     * @param PaymentService $paymentService
     */
    public function __construct(private readonly PaymentService $paymentService)
    {
        $this->paymentDriver = config('payment.default_driver');
    }

    /**
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $formUrl = $this->paymentService->createOrder(
            $this->paymentDriver,
            $request->get('amount'),
            $request->get('description'),
            OrderTypeRid::Purchase,
        );

        return response()->json(['formUrl' => $formUrl], Response::HTTP_CREATED);
    }

    /**
     * @param int $orderId
     * @return JsonResponse
     */
    public function getSimpleStatusById(int $orderId): JsonResponse
    {
        $simpleStatus = $this->paymentService->getSimpleStatusByOrderId(
            $this->paymentDriver,
            $orderId,
        );

        return response()->json(['simple_status' => $simpleStatus], $simpleStatus->httpCode);
    }
}
