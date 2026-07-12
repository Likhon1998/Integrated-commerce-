<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // Allowed mass assignable attributes
    protected $fillable = [
        'shop_id', 
        'user_id', 
        'customer_id', 
        'counter_id', 
        'invoice_no', 
        'total_amount', 
        'paid_amount', 
        'change_amount', 
        'payment_method',
        'status',
        'delivery_charge',
        
        // 🚀 NEW: Exchange & Return tracking fields
        'is_exchange_receipt',
        'exchange_for_order_id',
        'return_product_id',
        'return_qty',
        'exchange_credit',
    ];

    // The items on the receipt
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Link the order to the Cashier (User)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Link the order to the Shop
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    // Link the order to the Customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Link the order to the Counter (Hardware Terminal)
    public function counter()
    {
        return $this->belongsTo(Counter::class);
    }

    // 🚀 NEW: Link to the specific product they returned
    public function returnProduct()
    {
        return $this->belongsTo(Product::class, 'return_product_id');
    }

    // 🚀 NEW: Link this new receipt back to the original old receipt
    public function originalOrder()
    {
        return $this->belongsTo(Order::class, 'exchange_for_order_id');
    }
}