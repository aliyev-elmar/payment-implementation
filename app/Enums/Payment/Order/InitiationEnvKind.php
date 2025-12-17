<?php

namespace App\Enums\Payment\Order;

/* Indicates the environment kind of the order initiation */
enum InitiationEnvKind: string
{
    case MIT = 'Server'; // For MIT/Recurring operations
    case CIT = 'Browser'; // For CIT operations
}
