<?php

namespace App\Models;

use App\Casts\SanitizedHtml;
use App\Enums\ProductSort;
use App\Models\Concerns\HasSeoFields;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasSlugRedirects;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Number;

#[Fillable([
    'name', 'slug', 'sku', 'description', 'price', 'minimum_qty', 'is_active', 'is_featured', 'display_order',
    'meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'og_title', 'og_description', 'og_image', 'robots',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, HasSeoFields, HasSlug, HasSlugRedirects;

    protected function casts(): array
    {
        return [
            'description' => SanitizedHtml::class,
            'price' => 'decimal:2',
            'minimum_qty' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    /**
     * Price with the configured currency symbol, e.g. "₹1,250" or "₹2.50".
     *
     * @return Attribute<string, never>
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::get(fn (): string => SiteSetting::value('currency_symbol', '₹')
            .Number::format((float) $this->price, maxPrecision: 2, locale: 'en_IN'));
    }

    /**
     * The description as plain text, for excerpts and meta descriptions.
     *
     * @return Attribute<string, never>
     */
    protected function plainDescription(): Attribute
    {
        return Attribute::get(fn (): string => SanitizedHtml::plainText($this->description));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Schema.org Product data for search engines, built from the product's own details. The offer is
     * left out while prices are hidden, so search results never show a price the website does not.
     *
     * @return array<string, mixed>
     */
    public function structuredData(?Category $category = null): array
    {
        $url = route('products.show', $this);

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->meta_description ?: $this->plain_description,
            'image' => $this->images->map(fn (ProductImage $image): string => url($image->url))->all(),
            'category' => $category?->name,
            'url' => $url,
            'offers' => SiteSetting::value('show_prices', true) ? [
                '@type' => 'Offer',
                'url' => $url,
                'price' => number_format((float) $this->price, 2, '.', ''),
                'priceCurrency' => SiteSetting::value('currency_code', 'INR'),
                'availability' => 'https://schema.org/InStock',
                'itemCondition' => 'https://schema.org/NewCondition',
                'eligibleQuantity' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => $this->minimum_qty,
                ],
                'seller' => [
                    '@type' => 'Organization',
                    'name' => SiteSetting::value('site_name', config('app.name')),
                ],
            ] : null,
        ], fn (mixed $value): bool => filled($value));
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withPivot('display_order');
    }

    /**
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * @return HasOne<ProductImage, $this>
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->oldestOfMany('sort_order');
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('display_order')->latest();
    }

    /**
     * Products whose name or SKU contains every word of the search text.
     */
    #[Scope]
    protected function search(Builder $query, string $text): void
    {
        foreach (array_slice(preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY), 0, 5) as $word) {
            $query->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$word}%")
                ->orWhere('sku', 'like', "%{$word}%"));
        }
    }

    /**
     * Products priced from the minimum up to, but not including, the maximum.
     */
    #[Scope]
    protected function priceBetween(Builder $query, ?int $min, ?int $max): void
    {
        $query->when($min !== null, fn (Builder $query) => $query->where('price', '>=', $min))
            ->when($max !== null, fn (Builder $query) => $query->where('price', '<', $max));
    }

    /**
     * Products assigned to any of the given categories.
     *
     * @param  list<int>  $categoryIds
     */
    #[Scope]
    protected function inCategories(Builder $query, array $categoryIds): void
    {
        $query->whereHas('categories', fn (Builder $query) => $query->whereKey($categoryIds));
    }

    #[Scope]
    protected function sortedBy(Builder $query, ProductSort $sort): void
    {
        match ($sort) {
            ProductSort::Recommended => $query->ordered(),
            ProductSort::Newest => $query->latest(),
            ProductSort::PriceLowToHigh => $query->orderBy('price')->orderBy('name'),
            ProductSort::PriceHighToLow => $query->orderByDesc('price')->orderBy('name'),
            ProductSort::NameAToZ => $query->orderBy('name'),
        };

        // Rows that tie keep the same order from page to page.
        $query->orderByDesc('id');
    }
}
