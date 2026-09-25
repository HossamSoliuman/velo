<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Show an active product with its gallery, categories and related products.
     */
    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['images', 'categories' => fn ($query) => $query->visible()->ordered()->with('parent')]);

        // Breadcrumbs follow the most specific category, so a sub-category wins over its parent.
        $primaryCategory = $product->categories->sortByDesc(fn (Category $category) => $category->parent_id !== null)->first();

        return view('products.show', [
            'product' => $product,
            'primaryCategory' => $primaryCategory,
            'relatedProducts' => $product->categories->isEmpty() ? collect() : Product::query()
                ->active()
                ->whereKeyNot($product->id)
                ->inCategories($product->categories->modelKeys())
                ->ordered()
                ->with('primaryImage')
                ->limit(4)
                ->get(),
        ]);
    }
}
