<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Show the homepage with popular categories, featured products and new arrivals.
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
            'newArrivals' => Product::query()
                ->active()
                ->latest()
                ->latest('id')
                ->with('primaryImage')
                ->limit(4)
                ->get(),
        ]);
    }
}
