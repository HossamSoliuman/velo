<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsProducts;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SearchController extends Controller
{
    use ListsProducts;

    /**
     * Search active products by name or SKU, optionally narrowed by category and price.
     */
    public function __invoke(Request $request): View
    {
        $search = $request->query('q');
        $search = is_string($search) ? Str::limit(trim($search), 100, '') : '';
        $filters = $this->listingFilters($request);
        $category = $this->selectedCategory($request);

        $products = $search === '' ? null : $this->paginateProducts(
            Product::query()
                ->search($search)
                ->when($category, fn ($query) => $query->inCategories($category->listingCategoryIds())),
            $filters,
        );

        return view('products.search', [
            'search' => $search,
            'products' => $products,
            'selectedCategory' => $category,
            'filterCategories' => $this->filterCategories(),
            ...$this->listingViewData($request, $filters),
        ]);
    }
}
