<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Concerns\ShopScoped;
use App\Http\Controllers\Controller;
use App\Models\CmsBlog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogController extends Controller
{
    use ShopScoped;

    public function index()
    {
        $blogs = CmsBlog::where('shop_id', $this->shopId())->latest('published_at')->latest('id')->paginate(15);

        return view('cms.blogs.index', compact('blogs'));
    }

    public function create()
    {
        return view('cms.blogs.form', ['blog' => new CmsBlog(['is_published' => true, 'author_name' => auth()->user()->name])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['shop_id'] = $this->shopId();
        if ($request->hasFile('cover')) {
            $data['cover_image'] = $request->file('cover')->store('cms/blogs', 'public');
        }
        CmsBlog::create($data);

        return redirect()->route('cms.blogs.index')->with('success', 'Blog post saved. Live at /blog/'.$data['slug']);
    }

    public function edit(CmsBlog $blog)
    {
        $this->authorizeShop($blog);

        return view('cms.blogs.form', compact('blog'));
    }

    public function update(Request $request, CmsBlog $blog)
    {
        $this->authorizeShop($blog);
        $data = $this->validated($request, $blog);
        if ($request->hasFile('cover')) {
            if ($blog->cover_image) {
                Storage::disk('public')->delete($blog->cover_image);
            }
            $data['cover_image'] = $request->file('cover')->store('cms/blogs', 'public');
        }
        $blog->update($data);

        return redirect()->route('cms.blogs.index')->with('success', 'Blog post updated.');
    }

    public function destroy(CmsBlog $blog)
    {
        $this->authorizeShop($blog);
        if ($blog->cover_image) {
            Storage::disk('public')->delete($blog->cover_image);
        }
        $blog->delete();

        return back()->with('success', 'Blog post deleted.');
    }

    private function validated(Request $request, ?CmsBlog $blog = null): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable', 'string', 'max:255',
                Rule::unique('cms_blogs')->where(fn ($q) => $q->where('shop_id', $this->shopId()))->ignore($blog?->id),
            ],
            'excerpt' => 'nullable|string|max:500',
            'body' => 'nullable|string',
            'author_name' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
            'cover' => 'nullable|file|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ]);

        $data['slug'] = Str::slug($data['slug'] ?: $data['title']);
        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = $data['published_at'] ?? ($data['is_published'] ? now() : null);

        return $data;
    }
}
