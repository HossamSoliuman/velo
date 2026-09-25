<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'category' => Category::class,
            'product' => Product::class,
        ]);

        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        ResetPassword::createUrlUsing(fn (User $user, string $token) => route('admin.password.reset', [
            'token' => $token,
            'email' => $user->getEmailForPasswordReset(),
        ]));

        View::composer(['partials.site-header', 'partials.site-footer'], function ($view) {
            $view->with([
                'navigationCategories' => Category::navigation(),
                'priceRanges' => SiteSetting::priceRanges(),
            ]);
        });
    }
}
