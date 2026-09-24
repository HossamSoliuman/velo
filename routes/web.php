<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Phase 2 replaces these placeholders with the real catalogue and content pages.
Route::view('/categories', 'coming-soon', ['title' => 'All Categories'])->name('categories.index');
Route::get('/category/{slug}', fn (string $slug) => view('coming-soon', ['title' => str($slug)->headline()]))->name('categories.show');
Route::get('/product/{slug}', fn (string $slug) => view('coming-soon', ['title' => str($slug)->headline()]))->name('products.show');
Route::view('/price-range', 'coming-soon', ['title' => 'Shop by Price'])->name('price-range');
Route::view('/search', 'coming-soon', ['title' => 'Search'])->name('search');
Route::view('/about-us', 'coming-soon', ['title' => 'About Us'])->name('about');
Route::view('/e-catalog', 'coming-soon', ['title' => 'E-Catalog'])->name('e-catalog');
Route::view('/contact', 'coming-soon', ['title' => 'Contact Us'])->name('contact');
Route::view('/privacy-policy', 'coming-soon', ['title' => 'Privacy Policy'])->name('privacy');
Route::view('/terms-and-conditions', 'coming-soon', ['title' => 'Terms and Conditions'])->name('terms');
