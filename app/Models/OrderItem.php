<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal', // <-- THIS IS THE MISSING MAGIC WORD!
    ];

    // Allow OrderItem to find its parent Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Allow OrderItem to fetch the Product name and details
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}