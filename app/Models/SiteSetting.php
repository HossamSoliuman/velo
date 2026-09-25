<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Number;

#[Fillable(['key', 'value'])]
class SiteSetting extends Model
{
    public const CACHE_KEY = 'site_settings';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * Get a setting value by key, falling back to the given default.
     */
    public static function value(string $key, mixed $default = null): mixed
    {
        $settings = Cache::rememberForever(self::CACHE_KEY, fn () => self::query()->pluck('value', 'key')->all());

        return $settings[$key] ?? $default;
    }

    /**
     * Create or update a setting value.
     */
    public static function put(string $key, mixed $value): void
    {
        self::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Price ranges configured for browsing by budget, with display labels.
     *
     * @return list<array{min: int, max: int|null, label: string}>
     */
    public static function priceRanges(): array
    {
        $ranges = json_decode((string) self::value('price_ranges', '[]'), true) ?: [];

        return array_map(function (array $range): array {
            $min = (int) $range['min'];
            $max = $range['max'] === null ? null : (int) $range['max'];

            return ['min' => $min, 'max' => $max, 'label' => self::priceRangeLabel($min, $max)];
        }, $ranges);
    }

    /**
     * A price range label such as "Under ₹500", "₹500 – ₹1,000" or "Above ₹2,500".
     */
    public static function priceRangeLabel(?int $min, ?int $max): string
    {
        $format = fn (int $amount): string => self::value('currency_symbol', '₹').Number::format($amount, locale: 'en_IN');

        return match (true) {
            $max === null => 'Above '.$format($min ?? 0),
            ($min ?? 0) === 0 => 'Under '.$format($max),
            default => $format($min).' – '.$format($max),
        };
    }
}
