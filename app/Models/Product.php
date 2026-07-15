<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id', 'category_id', 'brand_id', 'name', 'barcode', 'sku',
        'variant_group', 'color', 'color_hex', 'storage',
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

    /** Sibling products in the same variant group (other colors / storage). */
    public function variantSiblings()
    {
        if (!$this->variant_group) {
            return collect();
        }

        return static::query()
            ->where('shop_id', $this->shop_id)
            ->where('variant_group', $this->variant_group)
            ->where('id', '!=', $this->id)
            ->where(function ($q) {
                $q->where('is_published', true)->orWhereNull('is_published');
            })
            ->where('stock_quantity', '>', 0)
            ->get();
    }

    public function displayColor(): ?string
    {
        return $this->color ?: null;
    }

    public function swatchHex(): string
    {
        if ($this->color_hex && preg_match('/^#[0-9A-Fa-f]{6}$/', $this->color_hex)) {
            return $this->color_hex;
        }

        $map = [
            'red' => '#dc2626',
            'blue' => '#2563eb',
            'black' => '#1e293b',
            'white' => '#f8fafc',
            'green' => '#16a34a',
            'gold' => '#ca8a04',
            'silver' => '#94a3b8',
            'gray' => '#64748b',
            'grey' => '#64748b',
            'pink' => '#ec4899',
            'purple' => '#9333ea',
            'orange' => '#ea580c',
            'yellow' => '#eab308',
            'natural titanium' => '#d4cfc8',
            'phantom black' => '#2d2d2d',
            'white titanium' => '#e8e6e3',
            'blue titanium' => '#5b7a9d',
            'black titanium' => '#3a3a3a',
        ];

        $key = strtolower(trim($this->color ?? ''));

        return $map[$key] ?? '#cbd5e1';
    }
}