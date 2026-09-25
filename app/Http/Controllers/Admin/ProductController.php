<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    use ManagesUploads;

    /**
     * Fields saved separately from the product's own columns.
     *
     * @var list<string>
     */
    private const NON_COLUMN_FIELDS = ['categories', 'images', 'primary_image', 'new_images', 'og_image', 'remove_og_image'];

    /**
     * List products with search and filters. Filtering by a category orders products as they appear in that category.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:active,inactive'],
            'featured' => ['nullable', 'boolean'],
        ]);

        $category = filled($filters['category'] ?? null) ? Category::query()->find($filters['category']) : null;
        $search = trim($filters['search'] ?? '');

        $query = $category !== null
            ? $category->products()->orderBy('category_product.display_order')->orderBy('products.name')
            : Product::query()->orderBy('display_order')->latest()->latest('id');

        $products = $query
            ->with(['primaryImage', 'categories:id,name'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('products.name', 'like', "%{$search}%")
                ->orWhere('products.sku', 'like', "%{$search}%")))
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('products.is_active', $filters['status'] === 'active'))
            ->when($request->boolean('featured'), fn ($query) => $query->where('products.is_featured', true))
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'category' => $category,
            'categoryOptions' => $this->categoryOptions(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'product' => new Product(['is_active' => true, 'is_featured' => false, 'minimum_qty' => 1, 'display_order' => 0]),
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = DB::transaction(function () use ($request) {
            $product = Product::query()->create([
                ...$request->safe()->except(self::NON_COLUMN_FIELDS),
                'display_order' => $request->integer('display_order'),
                'og_image' => $this->uploadedPath($request, 'og_image', null, 'seo'),
            ]);

            $product->categories()->sync($request->validated('categories'));
            $this->storeNewImages($request, $product);

            return $product;
        });

        return redirect()->route('admin.products.index')
            ->with('status', "Product “{$product->name}” created.")
            ->with('warnings', $product->seoWarnings());
    }

    public function edit(Product $product): View
    {
        $product->load(['images', 'categories:id']);

        return view('admin.products.edit', [
            'product' => $product,
            'categoryOptions' => $this->categoryOptions(),
            'seoWarnings' => $product->seoWarnings(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $previousOgImage = $product->og_image;

        DB::transaction(function () use ($request, $product, $previousOgImage) {
            $product->update([
                ...$request->safe()->except(self::NON_COLUMN_FIELDS),
                'display_order' => $request->integer('display_order'),
                'og_image' => $this->uploadedPath($request, 'og_image', $previousOgImage, 'seo', 'remove_og_image'),
            ]);

            $product->categories()->sync($request->validated('categories'));
            $this->updateExistingImages($request, $product);
            $this->storeNewImages($request, $product);
        });

        $this->deleteReplacedUpload($previousOgImage, $product->og_image);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product saved.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        DB::transaction(function () use ($product) {
            $product->images->each->delete();
            $product->delete();
        });

        $this->deleteUpload($product->og_image);

        return redirect()->route('admin.products.index')->with('status', "Product “{$product->name}” deleted.");
    }

    /**
     * Apply alt text, ordering and removals to the product's current images.
     */
    private function updateExistingImages(ProductRequest $request, Product $product): void
    {
        $input = $request->validated('images') ?? [];

        foreach ($product->images as $image) {
            $fields = $input[$image->id] ?? null;

            if ($fields === null) {
                continue;
            }

            if (! empty($fields['remove'])) {
                $image->delete();

                continue;
            }

            $image->update([
                'alt' => $fields['alt'] ?? null,
                'sort_order' => (int) ($fields['sort_order'] ?? $image->sort_order),
            ]);
        }
    }

    /**
     * Append uploaded images, then renumber the gallery so the chosen main image comes first.
     */
    private function storeNewImages(ProductRequest $request, Product $product): void
    {
        $position = (int) $product->images()->reorder()->max('sort_order') + 1;

        foreach ($request->file('new_images', []) as $file) {
            $product->images()->create([
                'path' => $file->store('products', 'public'),
                'sort_order' => $position++,
            ]);
        }

        $primaryImageId = $request->integer('primary_image');

        $product->images()->reorder()->orderBy('sort_order')->orderBy('id')->get()
            ->sortBy(fn (ProductImage $image) => $image->id === $primaryImageId ? 0 : 1)
            ->values()
            ->each(fn (ProductImage $image, int $index) => $image->update(['sort_order' => $index]));

        $product->unsetRelation('images');
    }

    /**
     * Top-level categories with their sub-categories, for pickers and filters.
     *
     * @return Collection<int, Category>
     */
    private function categoryOptions(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->ordered()
            ->with(['children' => fn ($query) => $query->ordered()])
            ->get(['id', 'name', 'parent_id', 'is_active']);
    }
}
