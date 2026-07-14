<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CmsBlog extends Model
{
    protected $table = 'cms_blogs';

    protected $fillable = [
        'shop_id', 'title', 'slug', 'excerpt', 'body',
        'cover_image', 'author_name', 'is_published', 'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $blog) {
            if (blank($blog->slug) && filled($blog->title)) {
                $blog->slug = Str::slug($blog->title);
            }
            if ($blog->is_published && blank($blog->published_at)) {
                $blog->published_at = now();
            }
        });
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }
}
