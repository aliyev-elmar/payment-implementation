<?php

namespace App\Repositories;

use App\Models\Basket;

class BasketRepository
{
    /**
     * @param int $userId
     * @return void
     */
    public function deleteByUserId(int $userId): void
    {
        Basket::query()->where('user_id', $userId)->delete();
    }
}
