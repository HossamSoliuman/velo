<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsProducts;
use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PriceRangeController extends Controller
{
    use ListsProducts;

    /**
     * Browse active products by budget, optionally narrowed by category.
     */
    public function __invoke(Request $request): View
    {
        $filters = $this->listingFilters($request);
        $category = $this->selectedCategory($request);

        $products = $this->paginateProducts(
            Product::query()->when($category, fn ($query) => $query->inCategories($category->listingCategoryIds())),
            $filters,
        );

        return view('products.price-range', [
            'heading' => ($filters['min'] ?? 0) === 0 && $filters['max'] === null
                ? 'Shop by Price'
                : SiteSetting::priceRangeLabel($filters['min'], $filters['max']),
            'products' => $products,
            'canonicalUrl' => $this->canonicalListingUrl($request, $products, ['min' => $filters['min'], 'max' => $filters['max']]),
            'selectedCategory' => $category,
            'filterCategories' => $this->filterCategories(),
            ...$this->listingViewData($request, $filters),
        ]);
    }
}
