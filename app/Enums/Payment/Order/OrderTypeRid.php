<?php

namespace App\Enums\Payment\Order;

enum OrderTypeRid: string
{
    case Purchase = 'Order_SMS';
    case PreAuth = 'Order_DMS';
}
