<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;

class WebsiteService
{
    public function shop(): ?Shop
    {
        // Single-store installation: one active shop for the whole system.
        return Shop::query()->where('is_active', true)->orderBy('id')->first();
    }

    public function shopId(): ?int
    {
        return $this->shop()?->id;
    }

    public function settings(): object
    {
        $shop = $this->shop();

        return (object) [
            'store_name' => $shop?->name ?? config('app.name', 'GAGET STORE'),
            'logo_path' => null,
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'special_offer_text' => 'Special Offer!',
            'trusted_by_text' => 'Trusted by 10,000+ customers worldwide',
            'contact_email' => $shop?->email,
            'contact_phone' => $shop?->phone,
            'contact_address' => $shop?->address,
        ];
    }

    public function homepageData(): array
    {
        $shopId = $this->shopId();
        $settings = $this->settings();

        if (!$shopId) {
            return $this->emptyHomepage($settings);
        }

        $categories = Category::where('shop_id', $shopId)
            ->withCount('products')
            ->orderBy('name')
            ->take(8)
            ->get();

        $bestSellers = Product::where('shop_id', $shopId)
            ->where('stock_quantity', '>', 0)
            ->with(['category', 'brand'])
            ->latest()
            ->take(8)
            ->get();

        $brands = Brand::where('shop_id', $shopId)
            ->where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->where('stock_quantity', '>', 0)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return [
            'settings' => $settings,
            'shop' => $this->shop(),
            'heroSlides' => collect(),
            'features' => collect(),
            'categories' => $categories,
            'allCategories' => Category::where('shop_id', $shopId)
                ->withCount(['products' => fn ($q) => $q->where('stock_quantity', '>', 0)])
                ->orderBy('name')
                ->get(),
            'promoBanners' => collect(),
            'bestSellers' => $bestSellers,
            'brands' => $brands,
            'mainNav' => collect(),
            'topBarNav' => collect(),
        ];
    }

    private function emptyHomepage(object $settings): array
    {
        return [
            'settings' => $settings,
            'shop' => null,
            'heroSlides' => collect(),
            'features' => collect(),
            'categories' => collect(),
            'allCategories' => collect(),
            'promoBanners' => collect(),
            'bestSellers' => collect(),
            'brands' => collect(),
            'mainNav' => collect(),
            'topBarNav' => collect(),
        ];
    }

    public function formatPrice(?float $amount, ?object $settings = null): string
    {
        $settings ??= $this->settings();
        $symbol = $settings->currency_symbol ?? '$';

        if ($amount === null) {
            return $symbol . '0.00';
        }

        return $symbol . number_format((float) $amount, 2);
    }

    public function productImageUrl($product): string
    {
        if ($product->image) {
            return public_storage_url($product->image);
        }

        return config('website_assets.products.' . $product->barcode)
            ?? config('website_assets.products.' . \Illuminate\Support\Str::slug($product->name))
            ?? 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&q=80';
    }

    public function categoryImageUrl($category): string
    {
        if ($category->image_path ?? null) {
            return public_storage_url($category->image_path);
        }

        $slug = $category->slug ?? \Illuminate\Support\Str::slug($category->name);

        return config('website_assets.categories.' . $slug)
            ?? 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&q=80';
    }
}
