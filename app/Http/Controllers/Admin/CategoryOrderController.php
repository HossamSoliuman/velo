<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryOrderController extends Controller
{
    /**
     * Save the display order of several categories at once.
     */
    public function update(Request $request): RedirectResponse
    {
        $order = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'integer', 'min:0', 'max:100000'],
        ])['order'];

        Category::query()
            ->whereKey(array_keys($order))
            ->get()
            ->each(fn (Category $category) => $category->update(['display_order' => (int) $order[$category->id]]));

        return back()->with('status', 'Category order saved.');
    }
}
