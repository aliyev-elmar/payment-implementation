<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\StoreRequest;
use App\Services\PaymentService;
use Illuminate\Http\{JsonResponse, Response};

class OrderController extends Controller
{
    /**
     * @param PaymentService $paymentService
     */
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    /**
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $formUrl = $this->paymentService->createOrder($request->get('amount'), $request->get('description'));
        return response()->json(['formUrl' => $formUrl], Response::HTTP_CREATED);
    }

    /**
     * @param int $orderId
     * @return JsonResponse
     */
    public function getSimpleStatusById(int $orderId): JsonResponse
    {
        $simpleStatus = $this->paymentService->getSimpleStatusByOrderId($orderId);
        return response()->json(['simple_status' => $simpleStatus], $simpleStatus->httpCode);
    }
}
