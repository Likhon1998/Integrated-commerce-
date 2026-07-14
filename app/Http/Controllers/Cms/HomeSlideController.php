<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Concerns\ShopScoped;
use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeSlideController extends Controller
{
    use ShopScoped;

    public function index()
    {
        $slides = HeroSlide::where('shop_id', $this->shopId())
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('cms.slides.index', compact('slides'));
    }

    public function create()
    {
        return view('cms.slides.form', ['slide' => new HeroSlide(['is_active' => true, 'button_text' => 'Shop Now', 'sort_order' => 0])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['shop_id'] = $this->shopId();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('cms/slides', 'public');
        }

        HeroSlide::create($data);

        return redirect()->route('cms.slides.index')->with('success', 'Home slide created. It will show on the website homepage.');
    }

    public function edit(HeroSlide $slide)
    {
        $this->authorizeShop($slide);

        return view('cms.slides.form', compact('slide'));
    }

    public function update(Request $request, HeroSlide $slide)
    {
        $this->authorizeShop($slide);
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            if ($slide->image_path) {
                Storage::disk('public')->delete($slide->image_path);
            }
            $data['image_path'] = $request->file('image')->store('cms/slides', 'public');
        }

        $slide->update($data);

        return redirect()->route('cms.slides.index')->with('success', 'Home slide updated.');
    }

    public function destroy(HeroSlide $slide)
    {
        $this->authorizeShop($slide);
        if ($slide->image_path) {
            Storage::disk('public')->delete($slide->image_path);
        }
        $slide->delete();

        return back()->with('success', 'Home slide deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'badge_text' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price_from' => 'nullable|numeric|min:0',
            'button_text' => 'nullable|string|max:100',
            'button_url' => 'nullable|string|max:255',
            'learn_more_url' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|file|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['button_text'] = $data['button_text'] ?: 'Shop Now';
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
