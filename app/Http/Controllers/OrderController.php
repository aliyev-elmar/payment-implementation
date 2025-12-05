<?php

namespace App\Http\Controllers;

use App\Enums\Payment\Order\OrderTypeRid;
use App\Http\Requests\Order\StoreRequest;
use App\Services\PaymentService;
use Illuminate\Http\{Response, JsonResponse};
use App\Exceptions\{PaymentGatewayException, OrderNotFoundException};

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
        try {
            $formUrl = $this->paymentService->createOrder(
                $this->paymentDriver,
                $request->get('amount'),
                $request->get('description'),
                OrderTypeRid::Purchase,
            );

            return response()->json(['formUrl' => $formUrl], Response::HTTP_CREATED);
        } catch (PaymentGatewayException $e) {
            return response()->json(['message' => 'Payment gateway error', 'details' => $e->getMessage()], $e->statusCode);
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
            $simpleStatusResponse = $this->paymentService->getSimpleStatusByOrderId($this->paymentDriver, $orderId);
            return response()->json(['order' => $simpleStatusResponse->order], $simpleStatusResponse->httpCode);
        } catch (OrderNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (PaymentGatewayException $e) {
            return response()->json(['message' => 'Payment gateway error', 'details' => $e->getMessage()], $e->statusCode);
        } catch (\Exception) {
            return response()->json(['message' => 'Internal server error while fetching order simple status'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
