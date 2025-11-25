<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class Basket extends Model
{
    use HasFactory;

    /**
     * @var array
     */
    protected $fillable = [
        'euser_id',
        'course_id',
    ];

    /**
     * @return HasOne
     */
    public function course(): HasOne
    {
        return $this->hasOne(Course::class,'id','course_id');
    }
}
