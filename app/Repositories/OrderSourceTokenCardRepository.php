<?php

namespace App\Repositories;

use App\DataTransferObjects\Payment\Order\SetSourceToken\SourceTokenCardDto;
use App\Models\OrderSourceTokenCard;

class OrderSourceTokenCardRepository
{
    /**
     * @param int $orderSourceTokenId
     * @param SourceTokenCardDto $dto
     * @return OrderSourceTokenCard
     */
    public function create(int $orderSourceTokenId, SourceTokenCardDto $dto) : OrderSourceTokenCard
    {
        return OrderSourceTokenCard::query()->create([
            'order_source_token_id' => $orderSourceTokenId,
            'expiration' => $dto->expiration,
            'brand' => $dto->brand,
        ]);
    }
}
