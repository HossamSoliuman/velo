<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $productCounts = Product::query()
            ->toBase()
            ->selectRaw('count(*) as total')
            ->selectRaw('count(case when is_active = ? then 1 end) as active', [true])
            ->selectRaw('count(case when is_featured = ? then 1 end) as featured', [true])
            ->first();

        $categoryCounts = Category::query()
            ->toBase()
            ->selectRaw('count(*) as total')
            ->selectRaw('count(case when is_active = ? then 1 end) as active', [true])
            ->first();

        return view('admin.dashboard', [
            'stats' => [
                'products' => (int) $productCounts->total,
                'activeProducts' => (int) $productCounts->active,
                'featuredProducts' => (int) $productCounts->featured,
                'categories' => (int) $categoryCounts->total,
                'activeCategories' => (int) $categoryCounts->active,
            ],
            'recentProducts' => Product::query()->with('primaryImage')->latest('updated_at')->latest('id')->limit(5)->get(),
            'hasECatalog' => filled(SiteSetting::value('e_catalog_path')),
        ]);
    }
}
