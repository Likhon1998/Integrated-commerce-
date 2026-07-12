<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'default_shop_id', 'store_name', 'logo_path',
        'currency_code', 'currency_symbol', 'special_offer_text',
        'trusted_by_text', 'contact_email', 'contact_phone',
        'contact_address', 'social_links',
    ];

    protected $casts = [
        'social_links' => 'array',
    ];

    public function defaultShop()
    {
        return $this->belongsTo(Shop::class, 'default_shop_id');
    }

    public static function current(): self
    {
        return static::query()->first() ?? new static([
            'store_name' => config('app.name', 'GAGET STORE'),
            'currency_code' => 'USD',
            'currency_symbol' => '$',
        ]);
    }
}
