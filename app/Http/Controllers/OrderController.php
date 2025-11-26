<?php

namespace App\Http\Controllers;

use App\Contracts\ICreateOrderService;
use App\Exceptions\Payment\CreateOrderException;
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
     * @throws \Throwable
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
     */
    public function getStatusById(int $orderId): JsonResponse
    {
        try {
            $isPaid = $this->createOrderService->checkStatusById($orderId);

            return response()->json([
                'status' => $isPaid ? 'paid' : 'pending',
                'message' => $isPaid ? 'Payment completed successfully' : 'Payment is pending'
            ]);

        } catch (\Exception $exception) {
            return response()->json([
                'message' => 'Error checking payment status',
                'error' => $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
