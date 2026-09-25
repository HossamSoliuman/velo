<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Show the contact details, map and enquiry form. A "product" slug in the query string attaches that product.
     */
    public function show(Request $request): View
    {
        $slug = $request->query('product');

        return view('contact', [
            'product' => is_string($slug) && $slug !== ''
                ? Product::query()->active()->where('slug', $slug)->with('primaryImage')->first()
                : null,
        ]);
    }
}
