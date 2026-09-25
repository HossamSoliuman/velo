<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    use ManagesUploads;

    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::query()
                ->whereNull('parent_id')
                ->ordered()
                ->withCount('products')
                ->with(['children' => fn ($query) => $query->ordered()->withCount('products')])
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create', [
            'category' => new Category(['is_active' => true, 'show_in_menu' => true, 'display_order' => 0]),
            'parentOptions' => $this->parentOptions(),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = Category::query()->create([
            ...$request->safe()->except(['image', 'remove_image', 'og_image', 'remove_og_image']),
            'display_order' => $request->integer('display_order'),
            'image' => $this->uploadedPath($request, 'image', null, 'categories'),
            'og_image' => $this->uploadedPath($request, 'og_image', null, 'seo'),
        ]);

        return redirect()->route('admin.categories.index')
            ->with('status', "Category “{$category->name}” created.")
            ->with('warnings', $category->seoWarnings());
    }

    public function edit(Category $category): View
    {
        $category->loadCount(['products', 'children']);

        return view('admin.categories.edit', [
            'category' => $category,
            'parentOptions' => $this->parentOptions($category),
            'reassignOptions' => Category::query()->whereKeyNot($category->id)->ordered()->get(['id', 'name', 'parent_id']),
            'seoWarnings' => $category->seoWarnings(),
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $previousImage = $category->image;
        $previousOgImage = $category->og_image;

        $category->update([
            ...$request->safe()->except(['image', 'remove_image', 'og_image', 'remove_og_image']),
            'display_order' => $request->integer('display_order'),
            'image' => $this->uploadedPath($request, 'image', $previousImage, 'categories', 'remove_image'),
            'og_image' => $this->uploadedPath($request, 'og_image', $previousOgImage, 'seo', 'remove_og_image'),
        ]);

        $this->deleteReplacedUpload($previousImage, $category->image);
        $this->deleteReplacedUpload($previousOgImage, $category->og_image);

        return redirect()->route('admin.categories.edit', $category)->with('status', 'Category saved.');
    }

    /**
     * Delete a category. Its products must first be moved to another category, and its sub-categories move up a level.
     */
    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $productIds = $category->products()->pluck('products.id');

        $validated = $request->validate([
            'reassign_to' => [
                Rule::requiredIf($productIds->isNotEmpty()),
                'nullable',
                'integer',
                Rule::exists(Category::class, 'id'),
                Rule::notIn([$category->id]),
            ],
        ], [
            'reassign_to.required' => 'Choose a category to move this category’s products to before deleting it.',
        ]);

        $target = $productIds->isNotEmpty() ? Category::query()->findOrFail($validated['reassign_to']) : null;

        DB::transaction(function () use ($category, $productIds, $target) {
            $target?->products()->syncWithoutDetaching($productIds->all());
            $category->children()->update(['parent_id' => $category->parent_id]);
            $category->delete();
        });

        $this->deleteUpload($category->image);
        $this->deleteUpload($category->og_image);

        $message = "Category “{$category->name}” deleted.";

        if ($target !== null) {
            $message .= sprintf(' %d %s moved to “%s”.', $productIds->count(), str('product')->plural($productIds->count()), $target->name);
        }

        return redirect()->route('admin.categories.index')->with('status', $message);
    }

    /**
     * Top-level categories that the given category may be placed under.
     *
     * @return Collection<int, Category>
     */
    private function parentOptions(?Category $category = null): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->when($category?->exists, fn ($query) => $query->whereKeyNot($category->id))
            ->ordered()
            ->get(['id', 'name']);
    }
}
