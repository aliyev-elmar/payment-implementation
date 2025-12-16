<?php

namespace App\Http\Controllers;

use App\Enums\Payment\Order\OrderTypeRid;
use App\Services\OrderService;
use App\Http\Requests\Order\StoreRequest;
use Illuminate\Http\{Response, JsonResponse};

class OrderController extends Controller
{
    /**
     * @param OrderService $orderService
     */
    public function __construct(private readonly OrderService $orderService)
    {
    }

    /**
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $formUrl = $this->orderService->create(
            config('payment.default_driver'),
            OrderTypeRid::Purchase,
            $request->get('amount'),
            $request->get('description', 'description for create order process'),
        );

        return response()->json(['formUrl' => $formUrl], Response::HTTP_CREATED);
    }

    /**
     * @param int $orderId
     * @return JsonResponse
     */
    public function setSourceTokenById(int $orderId): JsonResponse
    {
        $response = $this->orderService->setSourceToken(config('payment.default_driver'), $orderId);
        return response()->json(['order' => $response->order]);
    }

    /**
     * @param int $orderId
     * @return JsonResponse
     */
    public function getSimpleStatusById(int $orderId): JsonResponse
    {
        $response = $this->orderService->getSimpleStatusByOrderId(config('payment.default_driver'), $orderId);
        return response()->json(['order' => $response->order]);
    }
}
