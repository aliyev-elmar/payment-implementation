<?php

namespace App\Services;

use App\Repositories\BasketRepository;

class BasketService
{
    /**
     * @param BasketRepository $basketRepository
     */
    public function __construct(private readonly BasketRepository $basketRepository)
    {
    }

    /**
     * @param int $userId
     * @return void
     */
    public function deleteByUserId(int $userId): void
    {
        $this->basketRepository->deleteByUserId($userId);
    }
}
