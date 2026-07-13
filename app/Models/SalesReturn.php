<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    protected $fillable = [
        'shop_id', 'order_id', 'user_id', 'return_number', 'status', 'total_refund', 'notes',
    ];

    protected $casts = ['total_refund' => 'decimal:2'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(SalesReturnItem::class);
    }
}
