<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CmsBlog;
use App\Models\CmsFaq;
use App\Models\CmsPage;
use App\Models\CmsReview;
use App\Models\HeroSlide;
use App\Models\NavigationLink;
use App\Models\Product;
use App\Models\PromoBanner;
use App\Models\Shop;
use App\Models\SiteFeature;
use App\Models\SiteSetting;

class WebsiteService
{
    public function shop(): ?Shop
    {
        $settings = SiteSetting::query()->first();
        if ($settings?->default_shop_id) {
            $shop = Shop::query()->where('id', $settings->default_shop_id)->where('is_active', true)->first();
            if ($shop) {
                return $shop;
            }
        }

        return Shop::query()->where('is_active', true)->orderBy('id')->first();
    }

    public function shopId(): ?int
    {
        return $this->shop()?->id;
    }

    public function settings(): object
    {
        $site = SiteSetting::current();
        $shop = $this->shop();

        return (object) [
            'store_name' => $site->store_name ?: ($shop?->name ?? config('app.name', 'GAGET STORE')),
            'logo_path' => $site->logo_path,
            'currency_code' => $site->currency_code ?: 'USD',
            'currency_symbol' => $site->currency_symbol ?: '$',
            'special_offer_text' => $site->special_offer_text ?: 'Special Offer!',
            'trusted_by_text' => $site->trusted_by_text ?: 'Trusted by thousands of customers',
            'contact_email' => $site->contact_email ?: $shop?->email,
            'contact_phone' => $site->contact_phone ?: $shop?->phone,
            'contact_address' => $site->contact_address ?: $shop?->address,
            'social_links' => $site->social_links ?? [],
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

        $bestSellers = $this->catalogQuery($shopId)
            ->with(['category', 'brand'])
            ->orderByDesc('is_best_seller')
            ->orderByDesc('review_count')
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
            'heroSlides' => HeroSlide::where('shop_id', $shopId)->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
            'features' => SiteFeature::where('shop_id', $shopId)->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
            'categories' => $categories,
            'allCategories' => Category::where('shop_id', $shopId)
                ->withCount(['products' => fn ($q) => $q->where('stock_quantity', '>', 0)])
                ->orderBy('name')
                ->get(),
            'promoBanners' => PromoBanner::where('shop_id', $shopId)->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
            'bestSellers' => $bestSellers,
            'brands' => $brands,
            'mainNav' => NavigationLink::where('shop_id', $shopId)->where('location', 'main_nav')->where('is_active', true)->orderBy('sort_order')->get(),
            'topBarNav' => NavigationLink::where('shop_id', $shopId)->where('location', 'top_bar')->where('is_active', true)->orderBy('sort_order')->get(),
            'featuredReviews' => CmsReview::where('shop_id', $shopId)->where('is_published', true)->where('is_featured', true)->orderBy('sort_order')->take(6)->get(),
            'footerPages' => CmsPage::where('shop_id', $shopId)->where('is_published', true)->where('show_in_footer', true)->orderBy('sort_order')->get(),
            'latestBlogs' => CmsBlog::where('shop_id', $shopId)->published()->latest('published_at')->take(3)->get(),
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
            'featuredReviews' => collect(),
            'footerPages' => collect(),
            'latestBlogs' => collect(),
        ];
    }

    public function publishedPage(string $slug): ?CmsPage
    {
        $shopId = $this->shopId();
        if (!$shopId) {
            return null;
        }

        return CmsPage::where('shop_id', $shopId)->where('slug', $slug)->where('is_published', true)->first();
    }

    public function publishedBlog(string $slug): ?CmsBlog
    {
        $shopId = $this->shopId();
        if (!$shopId) {
            return null;
        }

        return CmsBlog::where('shop_id', $shopId)->where('slug', $slug)->published()->first();
    }

    public function publishedFaqs()
    {
        $shopId = $this->shopId();
        if (!$shopId) {
            return collect();
        }

        return CmsFaq::where('shop_id', $shopId)->where('is_published', true)->orderBy('sort_order')->orderBy('id')->get();
    }

    public function publishedBlogs(int $perPage = 9)
    {
        $shopId = $this->shopId();
        if (!$shopId) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        return CmsBlog::where('shop_id', $shopId)->published()->latest('published_at')->paginate($perPage);
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

    /** Products visible on the public storefront. */
    public function catalogQuery(?int $shopId = null)
    {
        $shopId ??= $this->shopId();

        return Product::query()
            ->where('shop_id', $shopId)
            ->where('stock_quantity', '>', 0)
            ->where(function ($q) {
                $q->where('is_published', true)->orWhereNull('is_published');
            });
    }

    public function productImageUrl($product): string
    {
        $urls = $this->productImageUrls($product);

        return $urls[0] ?? 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&q=80';
    }

    /** All product gallery URLs (uploaded images, then config/fallback). */
    public function productImageUrls($product): array
    {
        $urls = [];
        foreach ($product->imagePaths() as $path) {
            $urls[] = public_storage_url($path);
        }

        if ($urls === []) {
            $fallback = config('website_assets.products.' . $product->barcode)
                ?? config('website_assets.products.' . \Illuminate\Support\Str::slug($product->name))
                ?? 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&q=80';
            $urls[] = $fallback;
        }

        return $urls;
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
