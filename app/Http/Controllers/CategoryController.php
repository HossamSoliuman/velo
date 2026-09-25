<?php

namespace App\Http\Controllers;

use App\Enums\ProductSort;
use App\Http\Controllers\Concerns\ListsProducts;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CategoryController extends Controller
{
    use ListsProducts;

    /**
     * List every active top-level category with its sub-categories and product count.
     */
    public function index(): View
    {
        return view('categories.index', [
            'categories' => Category::query()
                ->active()
                ->whereNull('parent_id')
                ->ordered()
                ->with(['children' => fn ($query) => $query->active()->ordered()])
                ->get(),
            'productCounts' => $this->productCounts(),
        ]);
    }

    /**
     * List the active products in a category and its active sub-categories.
     */
    public function show(Request $request, Category $category): View
    {
        $category->load(['parent', 'children' => fn ($query) => $query->active()->ordered()]);

        abort_unless($category->isVisible(), 404);

        $filters = $this->listingFilters($request);
        $categoryIds = $category->listingCategoryIds();

        $query = Product::query()
            ->inCategories($categoryIds)
            ->when($filters['sort'] === ProductSort::Recommended, fn ($query) => $query->orderBy(
                DB::table('category_product')
                    ->selectRaw('min(display_order)')
                    ->whereColumn('category_product.product_id', 'products.id')
                    ->whereIn('category_product.category_id', $categoryIds),
            ));

        return view('categories.show', [
            'category' => $category,
            'subCategories' => $category->parent_id === null
                ? $category->children
                : $category->parent->children()->active()->ordered()->get(),
            'products' => $this->paginateProducts($query, $filters),
            ...$this->listingViewData($request, $filters),
        ]);
    }

    /**
     * Number of distinct active products per top-level category, counting its active sub-categories too.
     *
     * @return Collection<int, int>
     */
    private function productCounts(): Collection
    {
        return DB::table('category_product')
            ->join('categories', 'categories.id', '=', 'category_product.category_id')
            ->join('products', 'products.id', '=', 'category_product.product_id')
            ->where('categories.is_active', true)
            ->where('products.is_active', true)
            ->groupByRaw('coalesce(categories.parent_id, categories.id)')
            ->selectRaw('coalesce(categories.parent_id, categories.id) as top_level_id, count(distinct category_product.product_id) as product_count')
            ->pluck('product_count', 'top_level_id')
            ->map(fn (mixed $count) => (int) $count);
    }
}
