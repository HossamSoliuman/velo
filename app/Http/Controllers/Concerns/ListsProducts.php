<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\ProductSort;
use App\Models\Category;
use App\Models\Product;
use App\Models\SiteSetting;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * Price filtering, sorting and pagination shared by the public product listings.
 */
trait ListsProducts
{
    /**
     * The visitor's price and sort choices. Values that make no sense are ignored rather than rejected,
     * so a mistyped link still shows products.
     *
     * @return array{min: int|null, max: int|null, sort: ProductSort}
     */
    protected function listingFilters(Request $request): array
    {
        $min = $this->wholeNumberParameter($request, 'min');
        $max = $this->wholeNumberParameter($request, 'max');
        $sort = $request->query('sort');

        return [
            'min' => $min,
            'max' => $max !== null && $max > ($min ?? 0) ? $max : null,
            'sort' => (is_string($sort) ? ProductSort::tryFrom($sort) : null) ?? ProductSort::Recommended,
        ];
    }

    /**
     * Active products from the given query, filtered by price, sorted and split into pages.
     *
     * @param  Builder<Product>  $query
     * @param  array{min: int|null, max: int|null, sort: ProductSort}  $filters
     * @return LengthAwarePaginator<int, Product>
     */
    protected function paginateProducts(Builder $query, array $filters): LengthAwarePaginator
    {
        return $query->active()
            ->priceBetween($filters['min'], $filters['max'])
            ->sortedBy($filters['sort'])
            ->with('primaryImage')
            ->paginate(24)
            ->withQueryString();
    }

    /**
     * The visible category chosen in the "category" query parameter, if any.
     */
    protected function selectedCategory(Request $request): ?Category
    {
        $slug = $request->query('category');

        return is_string($slug) && $slug !== ''
            ? Category::query()->visible()->where('slug', $slug)->first()
            : null;
    }

    /**
     * Top-level categories and their active sub-categories, for the category filter.
     *
     * @return Collection<int, Category>
     */
    protected function filterCategories(): Collection
    {
        return Category::query()
            ->active()
            ->whereNull('parent_id')
            ->ordered()
            ->with(['children' => fn ($query) => $query->active()->ordered()])
            ->get();
    }

    /**
     * Data every listing view needs besides the products themselves. "listingUrl" builds a link to the
     * current listing with some query parameters changed (null removes one) and the page reset.
     *
     * @param  array{min: int|null, max: int|null, sort: ProductSort}  $filters
     * @return array{filters: array{min: int|null, max: int|null, sort: ProductSort}, priceRanges: list<array{min: int, max: int|null, label: string}>, sortOptions: list<ProductSort>, listingUrl: Closure(array<string, mixed>): string}
     */
    protected function listingViewData(Request $request, array $filters): array
    {
        return [
            'filters' => $filters,
            'priceRanges' => SiteSetting::priceRanges(),
            'sortOptions' => ProductSort::cases(),
            'listingUrl' => function (array $changes) use ($request): string {
                $query = Arr::query(array_filter(
                    [...$request->query(), ...$changes, 'page' => null],
                    fn (mixed $value) => $value !== null && $value !== '',
                ));

                return $request->url().($query === '' ? '' : '?'.$query);
            },
        ];
    }

    private function wholeNumberParameter(Request $request, string $key): ?int
    {
        $value = $request->query($key);

        return is_string($value) && ctype_digit($value) && strlen($value) <= 9 ? (int) $value : null;
    }
}
