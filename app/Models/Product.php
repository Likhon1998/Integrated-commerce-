<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id', 'category_id', 'brand_id', 'name', 'barcode', 'sku',
        'cost_price', 'selling_price', 'original_price', 'stock_quantity', 'alert_quantity', 'reorder_quantity',
        'image', 'image_2', 'image_3',
        'short_description', 'brand_name', 'rating', 'review_count',
        'is_best_seller', 'is_featured', 'is_new_arrival', 'is_published',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'rating' => 'decimal:1',
        'is_best_seller' => 'boolean',
        'is_featured' => 'boolean',
        'is_new_arrival' => 'boolean',
        'is_published' => 'boolean',
    ];

    /** Stored image paths (slot 1–3), skipping empty slots. */
    public function imagePaths(): array
    {
        return array_values(array_filter([
            $this->image,
            $this->image_2,
            $this->image_3,
        ]));
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class)->latest();
    }
}