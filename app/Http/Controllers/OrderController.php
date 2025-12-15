<?php

namespace App\Http\Controllers;

use App\Enums\Payment\Order\OrderTypeRid;
use App\Http\Requests\Order\StoreRequest;
use App\Services\OrderService;
use Illuminate\Http\{Response, JsonResponse};
use App\Exceptions\{InvalidRequestException, OrderNotFoundException};

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
        try {
            $formUrl = $this->orderService->create(
                config('payment.default_driver'),
                OrderTypeRid::Purchase,
                $request->get('amount'),
                $request->get('description', 'description for create order process'),
            );

            return response()->json(['formUrl' => $formUrl], Response::HTTP_CREATED);
        } catch (InvalidRequestException $e) {
            return response()->json(['message' => 'Invalid Request Exception', 'details' => $e->getMessage()], $e->statusCode);
        } catch (\Exception) {
            return response()->json(['message' => 'Internal server error during order creation'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $orderId
     * @return JsonResponse
     */
    public function setSourceTokenById(int $orderId): JsonResponse
    {
        try {
            $response = $this->orderService->setSourceToken(
                config('payment.default_driver'),
                $orderId,
            );

            return response()->json(['order' => $response->order], $response->httpCode);

        } catch (\Exception) {
            return response()->json(['message' => 'Internal server error during order creation'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $orderId
     * @return JsonResponse
     */
    public function getSimpleStatusById(int $orderId): JsonResponse
    {
        try {
            $response = $this->orderService->getSimpleStatusByOrderId(
                config('payment.default_driver'),
                $orderId,
            );

            return response()->json(['order' => $response->order], $response->httpCode);
        } catch (OrderNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (InvalidRequestException $e) {
            return response()->json(['message' => 'Invalid Request Exception', 'details' => $e->getMessage()], $e->statusCode);
        } catch (\Exception) {
            return response()->json(['message' => 'Internal server error during order creation'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
