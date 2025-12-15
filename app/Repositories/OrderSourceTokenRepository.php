<?php

namespace App\Repositories;

use App\DataTransferObjects\Payment\Order\SetSourceToken\SourceTokenDto;
use App\Models\OrderSourceToken;

class OrderSourceTokenRepository
{
    /**
     * @param int $orderId
     * @param SourceTokenDto $dto
     * @return OrderSourceToken
     */
    public function create(int $orderId, SourceTokenDto $dto) : OrderSourceToken
    {
        return OrderSourceToken::query()->create([
            'order_id' => $orderId,
            'external_id' => $dto->id,
            'payment_method' => $dto->paymentMethod,
            'role' => $dto->role,
            'status' => $dto->status,
            'reg_time' => $dto->regTime,
            'display_name' => $dto->displayName,
        ]);
    }
}
