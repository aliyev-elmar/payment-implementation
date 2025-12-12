<?php

namespace App\Enums\Payment\Order;

/* Sifarişin növü */
enum OrderTypeRid: string
{
    case Purchase = 'Order_SMS'; // Alış əməliyyatları üçün
    case PreAuth = 'Order_DMS'; // Preauthorization əməliyyatları üçün
    case RepeatPurchase = 'Order_REC'; // Təkrar Alış əməliyyatları üçün (saxlanılan kart ilə əməliyyatlar)
    case RepeatPreAuth = 'DMSN3D'; // Təkrar Preauthorization əməliyyatları üçün (saxlanılan kart ilə əməliyyatlar)
    case CardToCard = 'OCT'; // Kartdan Karta əməliyyatlar üçün (Sifariş Kredit Əməliyyatı)
}
