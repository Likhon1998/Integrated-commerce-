<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories for the current shop.
     */
    public function index()
    {
        $categories = Category::where('shop_id', Auth::user()->shop_id)
            ->latest()
            ->get();

        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255',
                // Unique check only within the user's specific shop
                Rule::unique('categories')->where(fn ($query) => $query->where('shop_id', Auth::user()->shop_id))
            ],
        ]);

        Category::create([
            'shop_id' => Auth::user()->shop_id,
            'name'    => $request->name,
            'slug'    => Str::slug($request->name),
        ]);

        return redirect()->route('categories.index')->with('success', 'Category created successfully!');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category)
    {
        // SaaS Security: Block access if the category belongs to a different shop
        if ($category->shop_id !== Auth::user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category)
    {
        if ($category->shop_id !== Auth::user()->shop_id) {
            abort(403);
        }

        $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255',
                Rule::unique('categories')
                    ->where(fn ($query) => $query->where('shop_id', Auth::user()->shop_id))
                    ->ignore($category->id)
            ],
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        if ($category->shop_id !== Auth::user()->shop_id) {
            abort(403);
        }

        // Optional: Check if products exist in this category before deleting
        if ($category->products()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category containing products.');
        }

        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Category removed.');
    }
}