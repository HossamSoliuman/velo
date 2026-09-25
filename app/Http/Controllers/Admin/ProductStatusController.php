<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductStatusController extends Controller
{
    /**
     * Show, hide, feature or unfeature a product without opening the edit form.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update($request->validate([
            'is_active' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
        ]));

        return back()->with('status', "Product “{$product->name}” updated.");
    }
}
