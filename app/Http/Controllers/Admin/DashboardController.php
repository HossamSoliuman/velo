<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Enquiry;
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

        $enquiryCounts = Enquiry::query()
            ->toBase()
            ->selectRaw('count(*) as total')
            ->selectRaw('count(case when read_at is null then 1 end) as unread')
            ->first();

        return view('admin.dashboard', [
            'stats' => [
                'enquiries' => (int) $enquiryCounts->total,
                'unreadEnquiries' => (int) $enquiryCounts->unread,
                'products' => (int) $productCounts->total,
                'activeProducts' => (int) $productCounts->active,
                'featuredProducts' => (int) $productCounts->featured,
                'categories' => (int) $categoryCounts->total,
                'activeCategories' => (int) $categoryCounts->active,
            ],
            'recentEnquiries' => Enquiry::query()->latest()->latest('id')->limit(5)->get(),
            'recentProducts' => Product::query()->with('primaryImage')->latest('updated_at')->latest('id')->limit(5)->get(),
            'hasECatalog' => filled(SiteSetting::value('e_catalog_path')),
        ]);
    }
}
