<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CategoryOrderController;
use App\Http\Controllers\Admin\CategoryStatusController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ECatalogController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductOrderController;
use App\Http\Controllers\Admin\ProductStatusController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Loaded from bootstrap/app.php with the "web" middleware, the "/admin"
| URL prefix and the "admin." route name prefix.
|
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->middleware('throttle:10,1')->name('password.store');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::patch('category-order', [CategoryOrderController::class, 'update'])->name('category-order.update');
    Route::patch('categories/{category}/status', [CategoryStatusController::class, 'update'])->name('categories.status.update');
    Route::resource('categories', CategoryController::class)->except('show');

    Route::patch('product-order', [ProductOrderController::class, 'update'])->name('product-order.update');
    Route::patch('products/{product}/status', [ProductStatusController::class, 'update'])->name('products.status.update');
    Route::resource('products', ProductController::class)->except('show');

    Route::get('pages', [PageController::class, 'index'])->name('pages.index');
    Route::get('pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
    Route::put('pages/{page}', [PageController::class, 'update'])->name('pages.update');

    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('e-catalog', [ECatalogController::class, 'edit'])->name('e-catalog.edit');
    Route::put('e-catalog', [ECatalogController::class, 'update'])->name('e-catalog.update');
    Route::delete('e-catalog', [ECatalogController::class, 'destroy'])->name('e-catalog.destroy');

    Route::get('account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('account', [AccountController::class, 'update'])->name('account.update');
});
