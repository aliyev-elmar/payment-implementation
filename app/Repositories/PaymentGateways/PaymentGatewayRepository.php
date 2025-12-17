<?php

namespace App\Repositories\PaymentGateways;

use App\Contracts\IPaymentGateway;
use App\Enums\Payment\Order\OrderTypeRid;

abstract class PaymentGatewayRepository implements IPaymentGateway
{
    /**
     * Operation Flow
     * Transaction flow (Common payment)
     *
     * @param int $amount
     * @param string $description
     * @return void
     */
    public function commonPaymentFlow(int $amount, string $description): void
    {
        /**
         * 1. Send create order request (Order_SMS).
         *  If success response go to step 2
         */
        $createOrderResponseDto = $this->createOrder(OrderTypeRid::Purchase, $amount, $description);
        $order = $createOrderResponseDto->order;

        /**
         * 2. Redirect to URL with values from Create Order response (point 1): Url Sample:
         *  {{order.hppUrl}}/flex?id={{order.id}}&password={{order.password}}
         */
        $formUrl = $createOrderResponseDto->formUrl;

        /**
         * 3. Redirect client with several (described below) transaction fields to redirect (callback ) url after transaction completion on PC (Processing center side).
         *  Transaction flow completed.Callback url sample:{{callback.url}}?ID=1234&STATUS=FullyPaid
         */

        /**
         * Note that STATUS parameter value can be temporary. So you have to verify transaction status using a Transaction details request.
         */
        $this->getSimpleStatusByOrderId($order->id);
    }
}
