<?php

namespace App\Contracts;

interface IOrderCreator
{
    /**
     * @param int $amount
     * @param string $description
     * @return array
     */
    public function getCreateOrderRequestBody(int $amount, string $description): array;
}
