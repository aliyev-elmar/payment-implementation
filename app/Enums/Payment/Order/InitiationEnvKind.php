<?php

namespace App\Enums\Payment\Order;

/* order.initiationEnvKind Sifarişin başlanğıc mühit növünü göstərir */
enum InitiationEnvKind: string
{
    case MIT = 'Server'; // MIT/Təkrar əməliyyatlar üçün
    case CIT = 'Browser'; // CIT əməliyyatları üçün
}
