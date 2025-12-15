<?php

namespace App\Enums\Payment;

enum ErrorCode: string
{
    case INVALID_TOKEN = 'InvalidToken';
    case INVALID_REQUEST = 'InvalidRequest';
    case INVALID_ORDER_STATE = 'InvalidOrderState';
}
