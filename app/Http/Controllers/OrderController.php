<?php

namespace App\Http\Controllers;

use App\Contracts\ICreateOrderService;
use App\Exceptions\Payment\CreateOrderException;
use App\Exceptions\Payment\GetOrderStatusException;
use App\Http\Requests\Order\StoreRequest;
use Illuminate\Http\{JsonResponse, Response};

class OrderController extends Controller
{
    /**
     * @param ICreateOrderService $createOrderService
     */
    public function __construct(private readonly ICreateOrderService $createOrderService)
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

            return response()->json([
                'status' => $isPaid ? 'FullyPaid' : 'payment doesn\'t completed',
                'message' => $isPaid ? 'Payment completed successfully' : 'Payment is pending'
            ]);
        } catch (GetOrderStatusException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->status);
        }
    }
}
