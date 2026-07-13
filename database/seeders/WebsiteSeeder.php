<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\NavigationLink;
use App\Models\Product;
use App\Models\PromoBanner;
use App\Models\Shop;
use App\Models\SiteFeature;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WebsiteSeeder extends Seeder
{
    public function run(): void
    {
        $shop = Shop::first();
        if (!$shop) {
            return;
        }

        SiteSetting::query()->delete();
        NavigationLink::where('shop_id', $shop->id)->delete();
        HeroSlide::where('shop_id', $shop->id)->delete();
        SiteFeature::where('shop_id', $shop->id)->delete();
        PromoBanner::where('shop_id', $shop->id)->delete();
        Brand::where('shop_id', $shop->id)->delete();

        SiteSetting::create([
            'default_shop_id' => $shop->id,
            'store_name' => 'GAGET STORE',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'special_offer_text' => 'Special Offer!',
            'trusted_by_text' => 'Trusted by 10,000+ customers worldwide',
            'contact_email' => 'hello@gagetstore.com',
            'contact_phone' => '+880 1700-000000',
            'contact_address' => 'Dhaka, Bangladesh',
        ]);

        HeroSlide::create([
            'shop_id' => $shop->id,
            'badge_text' => 'NEW ARRIVAL',
            'title' => 'iPhone 15 Pro Max',
            'description' => 'Titanium. So strong. So light. So Pro.',
            'price_from' => 1199.00,
            'button_text' => 'Shop Now',
            'button_url' => '/shop',
            'learn_more_url' => '/shop?filter=new',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $features = [
            ['icon' => 'truck', 'title' => 'Free Shipping', 'subtitle' => 'On all orders over $50', 'sort_order' => 1],
            ['icon' => 'return', 'title' => '30-Day Returns', 'subtitle' => 'Hassle-free returns', 'sort_order' => 2],
            ['icon' => 'lock', 'title' => 'Secure Payments', 'subtitle' => '100% secure payments', 'sort_order' => 3],
            ['icon' => 'shield', 'title' => '1 Year Warranty', 'subtitle' => 'Product warranty', 'sort_order' => 4],
            ['icon' => 'support', 'title' => '24/7 Support', 'subtitle' => 'Dedicated support', 'sort_order' => 5],
        ];
        foreach ($features as $f) {
            SiteFeature::create(array_merge($f, ['shop_id' => $shop->id, 'is_active' => true]));
        }

        $promos = [
            ['title' => 'Summer Sale', 'subtitle' => 'Up to 40% Off', 'theme' => 'dark', 'sort_order' => 1],
            ['title' => 'MacBook Air', 'subtitle' => 'Supercharged by M3', 'price_from' => 1099, 'theme' => 'light', 'sort_order' => 2],
            ['title' => 'Best Deals', 'subtitle' => 'Smartwatches', 'price_from' => 99, 'theme' => 'dark', 'sort_order' => 3],
        ];
        foreach ($promos as $p) {
            PromoBanner::create(array_merge($p, ['shop_id' => $shop->id, 'button_text' => 'Shop Now', 'button_url' => '/shop', 'is_active' => true]));
        }

        foreach (['Apple', 'Samsung', 'Sony', 'Bose', 'Canon', 'Dell', 'Xiaomi'] as $i => $name) {
            Brand::create(['shop_id' => $shop->id, 'name' => $name, 'sort_order' => $i + 1, 'is_active' => true]);
        }

        $navLinks = [
            ['label' => 'Home', 'url' => '/', 'location' => 'main_nav', 'sort_order' => 1],
            ['label' => 'Shop', 'url' => '/shop', 'location' => 'main_nav', 'sort_order' => 2],
            ['label' => 'Categories', 'url' => '/shop', 'location' => 'main_nav', 'sort_order' => 3],
            ['label' => 'Deals', 'url' => '/shop?filter=deals', 'location' => 'main_nav', 'sort_order' => 4],
            ['label' => 'New Arrivals', 'url' => '/shop?filter=new', 'location' => 'main_nav', 'sort_order' => 5],
            ['label' => 'Brands', 'url' => '/#brands', 'location' => 'main_nav', 'sort_order' => 6],
            ['label' => 'Blog', 'url' => '/login', 'location' => 'main_nav', 'sort_order' => 7],
            ['label' => 'Contact', 'url' => '/login', 'location' => 'main_nav', 'sort_order' => 8],
            ['label' => 'Free shipping on all orders over $50', 'url' => '/shop', 'location' => 'top_bar', 'sort_order' => 1],
            ['label' => '30-day easy returns', 'url' => '#', 'location' => 'top_bar', 'sort_order' => 2],
            ['label' => '1 Year Warranty', 'url' => '#', 'location' => 'top_bar', 'sort_order' => 3],
        ];
        foreach ($navLinks as $link) {
            NavigationLink::create(array_merge($link, ['shop_id' => $shop->id, 'is_active' => true]));
        }

        $categoryData = [
            ['name' => 'Smartphones', 'label' => '120+ Products'],
            ['name' => 'Laptops', 'label' => '85+ Products'],
            ['name' => 'Headphones', 'label' => '200+ Products'],
            ['name' => 'Smartwatches', 'label' => '60+ Products'],
            ['name' => 'Cameras', 'label' => '45+ Products'],
            ['name' => 'Accessories', 'label' => '300+ Products'],
            ['name' => 'Tablets', 'label' => '50+ Products'],
            ['name' => 'Gaming', 'label' => '90+ Products'],
        ];

        foreach ($categoryData as $cat) {
            $category = Category::firstOrCreate(
                ['shop_id' => $shop->id, 'name' => $cat['name']],
                ['slug' => Str::slug($cat['name'])]
            );
            $category->update([
                'is_featured' => true,
                'product_count_label' => $cat['label'],
                'slug' => Str::slug($cat['name']),
            ]);
        }

        $sampleProducts = [
            ['name' => 'Apple AirPods Pro (2nd Gen)', 'category' => 'Headphones', 'brand' => 'Apple', 'price' => 249, 'original' => 279, 'rating' => 4.8, 'reviews' => 2847, 'bestseller' => true],
            ['name' => 'Samsung Galaxy S24 Ultra', 'category' => 'Smartphones', 'brand' => 'Samsung', 'price' => 1199, 'original' => 1299, 'rating' => 4.9, 'reviews' => 1523, 'bestseller' => true],
            ['name' => 'Sony WH-1000XM5', 'category' => 'Headphones', 'brand' => 'Sony', 'price' => 348, 'original' => 399, 'rating' => 4.7, 'reviews' => 3102, 'bestseller' => true],
            ['name' => 'Apple Watch Series 9', 'category' => 'Smartwatches', 'brand' => 'Apple', 'price' => 399, 'original' => 429, 'rating' => 4.6, 'reviews' => 987, 'bestseller' => true],
            ['name' => 'Canon EOS R50', 'category' => 'Cameras', 'brand' => 'Canon', 'price' => 679, 'original' => 749, 'rating' => 4.5, 'reviews' => 456, 'bestseller' => true],
        ];

        foreach ($sampleProducts as $i => $p) {
            $category = Category::where('shop_id', $shop->id)->where('name', $p['category'])->first();
            Product::firstOrCreate(
                ['shop_id' => $shop->id, 'barcode' => 'WEB-DEMO-' . ($i + 1)],
                [
                    'category_id' => $category?->id,
                    'name' => $p['name'],
                    'cost_price' => $p['price'] * 0.7,
                    'selling_price' => $p['price'],
                    'original_price' => $p['original'],
                    'stock_quantity' => 0,
                    'brand_name' => $p['brand'],
                    'rating' => $p['rating'],
                    'review_count' => $p['reviews'],
                    'is_best_seller' => $p['bestseller'],
                    'is_published' => true,
                    'short_description' => 'Premium quality electronics with fast delivery.',
                ]
            );
        }
    }
}
