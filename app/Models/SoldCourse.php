<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class SoldCourse extends Model
{
    use HasFactory;

    /**
     * @var array
     */
    protected $fillable = [
        'euser_id',
        'course_id',
        'price',
        'currency',
        'student_profit',
        'end_date',
        'student_profit_money',
        'data',
        'is_gift',
        'status',
    ];

    /**
     * @return HasOne
     */
    public function course(): HasOne
    {
        return $this->hasOne(Course::class,'id','course_id');
    }

    /**
     * @return HasOne
     */
    public function euser(): HasOne
    {
        return $this->hasOne(Euser::class,'id','euser_id');
    }
}
