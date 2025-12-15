<?php

namespace App\Http\Controllers;

use App\Enums\Payment\Order\OrderTypeRid;
use App\Services\OrderService;
use App\Http\Requests\Order\StoreRequest;
use Illuminate\Http\Response;
use App\Exceptions\{
    InvalidRequestException,
    InvalidTokenException,
    InvalidOrderStateException,
    OrderNotFoundException,
};
use Exception;

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
     * @return Response
     * @throws InvalidRequestException
     */
    public function store(StoreRequest $request): Response
    {
        $formUrl = $this->orderService->create(
            config('payment.default_driver'),
            OrderTypeRid::Purchase,
            $request->get('amount'),
            $request->get('description', 'description for create order process'),
        );

        return successResponse(['formUrl' => $formUrl], Response::HTTP_CREATED);
    }

    /**
     * @param int $orderId
     * @return Response
     * @throws InvalidRequestException
     * @throws InvalidTokenException
     * @throws InvalidOrderStateException
     * @throws Exception
    */
    public function setSourceTokenById(int $orderId): Response
    {
        $response = $this->orderService->setSourceToken(config('payment.default_driver'), $orderId);
        return successResponse(['order' => $response->order]);
    }

    /**
     * @param int $orderId
     * @return Response
     * @throws InvalidRequestException
     * @throws OrderNotFoundException
     */
    public function getSimpleStatusById(int $orderId): Response
    {
        $response = $this->orderService->getSimpleStatusByOrderId(config('payment.default_driver'), $orderId);
        return successResponse(['order' => $response->order]);
    }
}
