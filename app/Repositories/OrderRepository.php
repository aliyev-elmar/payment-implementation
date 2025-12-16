<?php

namespace App\Repositories;

use App\DataTransferObjects\Payment\Order\CreateOrderDto;
use App\Models\Order;
use Illuminate\Support\Facades\Crypt;

class OrderRepository
{
    /**
     * @param CreateOrderDto $dto
     * @return Order
     */
    public function create(CreateOrderDto $dto): Order
    {
        return Order::query()->create([
            'external_id' => $dto->id,
            'hpp_url' => $dto->hppUrl,
            'password' => Crypt::encryptString($dto->password),
            'status' => $dto->status,
            'cvv2_auth_status' => $dto->cvv2AuthStatus,
            'secret' => Crypt::encryptString($dto->secret),
        ]);
    }

    /**
     * @param int $externalId
     * @return Order|null
     */
    public function getByExternalId(int $externalId): ?Order
    {
        return Order::query()->where('external_id', $externalId)->first();
    }

    /**
     * @param Order $order
     * @param string $status
     * @return Order
     */
    public function updateStatus(Order $order, string $status): Order
    {
        $order->status = $status;
        $order->save();
        return $order;
    }
}
