<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'address', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Shop $shop) {
            if (config('store.single_shop_mode', true) && static::query()->exists()) {
                throw new RuntimeException('Only one store is allowed in this system.');
            }
        });
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
