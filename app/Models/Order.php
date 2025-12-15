<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /**
     * @var array
     */
    public $guarded = [
        'id',
    ];

    /**
     * @return HasMany
     */
    public function source_tokens(): HasMany
    {
        return $this->hasMany(OrderSourceToken::class);
    }
}
