<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ECatalogController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PriceRangeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RobotsTxtController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');

// An address that no longer matches a slug permanently redirects if the record used that slug before.
Route::get('/category/{category}', [CategoryController::class, 'show'])
    ->name('categories.show')
    ->missing(fn (Request $request) => redirect()->route(
        'categories.show',
        Category::findByPreviousSlug((string) $request->route('category')) ?? abort(404),
        301,
    ));

Route::get('/product/{product}', [ProductController::class, 'show'])
    ->name('products.show')
    ->missing(fn (Request $request) => redirect()->route(
        'products.show',
        Product::findByPreviousSlug((string) $request->route('product')) ?? abort(404),
        301,
    ));

Route::get('/price-range', PriceRangeController::class)->name('price-range');
Route::get('/search', SearchController::class)->name('search');

Route::get('/about-us', [PageController::class, 'show'])->defaults('page', 'about-us')->name('about');
Route::get('/privacy-policy', [PageController::class, 'show'])->defaults('page', 'privacy-policy')->name('privacy');
Route::get('/terms-and-conditions', [PageController::class, 'show'])->defaults('page', 'terms-and-conditions')->name('terms');

Route::get('/e-catalog', [ECatalogController::class, 'show'])->name('e-catalog');
Route::get('/e-catalog/download', [ECatalogController::class, 'download'])->name('e-catalog.download');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsTxtController::class)->name('robots');
Route::post('/enquiries', [EnquiryController::class, 'store'])->middleware('throttle:enquiries')->name('enquiries.store');
