<?php

namespace App\Enums\Payment\Order;

enum OrderStatus: string
{
    case FULLY_PAID = 'FullyPaid';
    case PREPARING = 'Preparing';
    case EXPIRED = 'Expired';
}
