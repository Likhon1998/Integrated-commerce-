<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id', 'name', 'slug', 'image_path', 'description',
        'is_featured', 'product_count_label',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    // A category will have many products later
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}