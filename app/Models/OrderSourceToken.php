<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class OrderSourceToken extends Model
{
    /**
     * @var array
     */
    public $guarded = [
        'id',
    ];

    /**
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return HasMany
     */
    public function order_source_token_cards(): HasMany
    {
        $this->hasMany(OrderSourceTokenCard::class);
    }
}
