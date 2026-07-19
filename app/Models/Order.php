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
        'discount_amount',
        'paid_amount', 
        'change_amount', 
        'payment_method',
        'status',
        'delivery_charge',
        'shipping_courier',
        'shipping_tracking_no',
        
        // 🚀 NEW: Exchange & Return tracking fields
        'is_exchange_receipt',
        'exchange_for_order_id',
        'return_product_id',
        'return_qty',
        'exchange_credit',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'exchange_credit' => 'decimal:2',
        'is_exchange_receipt' => 'boolean',
    ];

    /** Gross − discount − exchange credit (what the customer owes). */
    public function netPayable(): float
    {
        return max(0, (float) $this->total_amount - (float) ($this->discount_amount ?? 0) - (float) ($this->exchange_credit ?? 0));
    }

    // The items on the receipt
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class)->orderBy('created_at');
    }

    public function isOnlineOrder(): bool
    {
        return $this->counter_id === null && str_starts_with((string) $this->invoice_no, 'WEB-');
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