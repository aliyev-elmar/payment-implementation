<?php

namespace App\Enums\Payment\Order;

/* Type of the order */
enum OrderTypeRid: string
{
    case Purchase = 'Order_SMS'; // For purchase operations
    case PreAuth = 'Order_DMS'; // For Preauthorization operations
    case RepeatPurchase = 'Order_REC'; // For recurring Purchase operations (transactions with saved card)
    case RepeatPreAuth = 'DMSN3D'; // For recurring Preauthorization operations (transactions with saved card)
    case CardToCard = 'OCT'; // For Account-to-Card operations (Order Credit Transaction)
}
