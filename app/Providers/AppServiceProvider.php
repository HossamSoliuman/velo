<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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

        RateLimiter::for('enquiries', function (Request $request) {
            $tooManyEnquiries = function (Request $request, array $headers) {
                $message = 'You have sent several enquiries in a short time. Please wait a few minutes, or call us instead.';

                return $request->expectsJson()
                    ? response()->json(['message' => $message], 429, $headers)
                    : back()->withInput()->withErrors(['enquiry' => $message]);
            };

            return [
                Limit::perMinute(5)->by('minute:'.$request->ip())->response($tooManyEnquiries),
                Limit::perDay(30)->by('day:'.$request->ip())->response($tooManyEnquiries),
            ];
        });

        View::composer('components.layouts.admin', function ($view) {
            $view->with('unreadEnquiries', auth()->check() ? Enquiry::query()->unread()->count() : 0);
        });

        View::composer(['partials.site-header', 'partials.site-footer'], function ($view) {
            $view->with([
                'navigationCategories' => Category::navigation(),
                'priceRanges' => SiteSetting::priceRanges(),
            ]);
        });
    }
}
