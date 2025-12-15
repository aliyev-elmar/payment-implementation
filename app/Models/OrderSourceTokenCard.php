<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderSourceTokenCard extends Model
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
    public function order_source_token(): BelongsTo
    {
        return $this->belongsTo(OrderSourceToken::class);
    }
}
