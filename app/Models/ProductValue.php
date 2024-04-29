<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductValue extends Model
{
    use HasFactory;

    public function value()
    {
        return $this->belongsTo(Value::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
