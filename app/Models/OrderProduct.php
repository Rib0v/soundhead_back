<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'count',
        'price',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
