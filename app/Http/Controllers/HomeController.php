<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Show the homepage with featured categories and products.
     */
    public function __invoke(): View
    {
        return view('home', [
            'categories' => Category::navigation(),
            'featuredProducts' => Product::query()
                ->active()
                ->featured()
                ->ordered()
                ->with('primaryImage')
                ->limit(8)
                ->get(),
        ]);
    }
}
