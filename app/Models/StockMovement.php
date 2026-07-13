<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'shop_id', 'product_id', 'user_id', 'type', 'reason',
        'quantity', 'previous_stock', 'current_stock', 'reference',
        'document_type', 'document_id', 'location_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(StockLocation::class, 'location_id');
    }
}