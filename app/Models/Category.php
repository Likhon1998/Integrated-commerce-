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

    protected static function booted(): void
    {
        static::saving(function (self $category) {
            if (blank($category->slug) && filled($category->name)) {
                $base = \Illuminate\Support\Str::slug($category->name) ?: 'category';
                $slug = $base;
                $i = 1;
                while (static::query()
                    ->where('shop_id', $category->shop_id)
                    ->when($category->exists, fn ($q) => $q->where('id', '!=', $category->id))
                    ->where('slug', $slug)
                    ->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $category->slug = $slug;
            }
        });
    }

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