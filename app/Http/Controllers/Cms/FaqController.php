<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Concerns\ShopScoped;
use App\Http\Controllers\Controller;
use App\Models\CmsFaq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use ShopScoped;

    public function index()
    {
        $faqs = CmsFaq::where('shop_id', $this->shopId())->orderBy('sort_order')->orderBy('id')->get();

        return view('cms.faqs.index', compact('faqs'));
    }

    public function create()
    {
        return view('cms.faqs.form', ['faq' => new CmsFaq(['is_published' => true, 'sort_order' => 0])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['shop_id'] = $this->shopId();
        CmsFaq::create($data);

        return redirect()->route('cms.faqs.index')->with('success', 'FAQ added to the website Help / FAQ page.');
    }

    public function edit(CmsFaq $faq)
    {
        $this->authorizeShop($faq);

        return view('cms.faqs.form', compact('faq'));
    }

    public function update(Request $request, CmsFaq $faq)
    {
        $this->authorizeShop($faq);
        $faq->update($this->validated($request));

        return redirect()->route('cms.faqs.index')->with('success', 'FAQ updated.');
    }

    public function destroy(CmsFaq $faq)
    {
        $this->authorizeShop($faq);
        $faq->delete();

        return back()->with('success', 'FAQ deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'category' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $data['is_published'] = $request->boolean('is_published');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
