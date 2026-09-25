<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Number;
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
                'priceRanges' => $this->priceRanges(),
            ]);
        });
    }

    /**
     * Price ranges configured in site settings, with display labels.
     *
     * @return list<array{min: int, max: int|null, label: string}>
     */
    private function priceRanges(): array
    {
        $symbol = SiteSetting::value('currency_symbol', '₹');
        $ranges = json_decode((string) SiteSetting::value('price_ranges', '[]'), true) ?: [];

        return array_map(fn (array $range) => [
            'min' => (int) $range['min'],
            'max' => $range['max'] === null ? null : (int) $range['max'],
            'label' => match (true) {
                $range['max'] === null => 'Above '.$symbol.Number::format($range['min'], locale: 'en_IN'),
                (int) $range['min'] === 0 => 'Under '.$symbol.Number::format($range['max'], locale: 'en_IN'),
                default => $symbol.Number::format($range['min'], locale: 'en_IN').' – '.$symbol.Number::format($range['max'], locale: 'en_IN'),
            },
        ], $ranges);
    }
}
