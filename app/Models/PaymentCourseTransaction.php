<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCourseTransaction extends Model
{
    use HasFactory;

    /**
     * @var array
     */
    protected $fillable = [
        'euser_id',
        'course_id',
        'myOrderId',
        'orderId',
        'amount',
        'currency',
        'language',
        'status',
        'data',
        'is_gift',
    ];

}
