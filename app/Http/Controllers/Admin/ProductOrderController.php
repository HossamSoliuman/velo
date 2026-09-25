<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductOrderController extends Controller
{
    /**
     * Save product positions, either within one category or in the overall product order.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['nullable', 'integer', Rule::exists(Category::class, 'id')],
            'order' => ['required', 'array'],
            'order.*' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);

        $order = $validated['order'];

        if (filled($validated['category'] ?? null)) {
            $category = Category::query()->findOrFail($validated['category']);

            foreach ($order as $productId => $position) {
                $category->products()->updateExistingPivot($productId, ['display_order' => (int) $position]);
            }

            return back()->with('status', "Product order in “{$category->name}” saved.");
        }

        Product::query()
            ->whereKey(array_keys($order))
            ->get()
            ->each(fn (Product $product) => $product->update(['display_order' => (int) $order[$product->id]]));

        return back()->with('status', 'Product order saved.');
    }
}
