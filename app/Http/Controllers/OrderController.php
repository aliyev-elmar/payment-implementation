<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\StoreRequest;
use App\Services\Payment\CreateOrderService;
use Illuminate\Http\{JsonResponse, Response};
use App\Exceptions\Payment\{CreateOrderException, GetOrderStatusException};

class OrderController extends Controller
{
    /**
     * @param CreateOrderService $createOrderService
     */
    public function __construct(private readonly CreateOrderService $createOrderService)
    {
    }

    /**
     * @param StoreRequest $request
     * @return JsonResponse
     * @throws CreateOrderException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        try {
            $response = $this->createOrderService->create($request->get('amount'), $request->get('description'));
            $formUrl = $this->createOrderService->getFormUrlByOrder($response->order);

            return response()->json(['formUrl' => $formUrl], $response->httpCode);
        } catch (CreateOrderException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $orderId
     * @return JsonResponse
     * @throws GetOrderStatusException
     */
    public function getStatusById(int $orderId): JsonResponse
    {
        try {
            $isPaid = $this->createOrderService->checkSimpleStatusById($orderId);

            return response()->json(['message' => $isPaid ? 'Payment completed successfully' : 'Payment is pending']);
        } catch (GetOrderStatusException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->statusCode);
        }
    }
}
