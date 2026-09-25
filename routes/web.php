<?php

<<<<<<< Updated upstream
=======
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ECatalogController;
use App\Http\Controllers\EnquiryController;
>>>>>>> Stashed changes
use App\Http\Controllers\HomeController;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Phase 2 replaces these placeholders with the real catalogue and content pages.
Route::view('/categories', 'coming-soon', ['title' => 'All Categories'])->name('categories.index');
Route::get('/category/{slug}', function (string $slug) {
    if ($category = Category::findByPreviousSlug($slug)) {
        return redirect()->route('categories.show', $category->slug, 301);
    }

    return view('coming-soon', ['title' => str($slug)->headline()]);
})->name('categories.show');
Route::get('/product/{slug}', function (string $slug) {
    if ($product = Product::findByPreviousSlug($slug)) {
        return redirect()->route('products.show', $product->slug, 301);
    }

<<<<<<< Updated upstream
    return view('coming-soon', ['title' => str($slug)->headline()]);
})->name('products.show');
Route::view('/price-range', 'coming-soon', ['title' => 'Shop by Price'])->name('price-range');
Route::view('/search', 'coming-soon', ['title' => 'Search'])->name('search');
Route::view('/about-us', 'coming-soon', ['title' => 'About Us'])->name('about');
Route::view('/e-catalog', 'coming-soon', ['title' => 'E-Catalog'])->name('e-catalog');
Route::view('/contact', 'coming-soon', ['title' => 'Contact Us'])->name('contact');
Route::view('/privacy-policy', 'coming-soon', ['title' => 'Privacy Policy'])->name('privacy');
Route::view('/terms-and-conditions', 'coming-soon', ['title' => 'Terms and Conditions'])->name('terms');
=======
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
Route::post('/enquiries', [EnquiryController::class, 'store'])->middleware('throttle:enquiries')->name('enquiries.store');
>>>>>>> Stashed changes
