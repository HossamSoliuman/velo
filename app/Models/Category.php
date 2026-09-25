<?php

namespace App\Models;

use App\Casts\SanitizedHtml;
use App\Models\Concerns\HasSeoFields;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasSlugRedirects;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'parent_id', 'name', 'slug', 'description', 'image', 'display_order', 'is_active', 'show_in_menu',
    'meta_title', 'meta_description', 'og_title', 'og_description', 'og_image', 'robots',
])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, HasSeoFields, HasSlug, HasSlugRedirects;

    public const NAVIGATION_CACHE_KEY = 'navigation.categories';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::NAVIGATION_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::NAVIGATION_CACHE_KEY));
    }

    /**
     * Active, menu-visible top-level categories with their active, menu-visible children.
     *
     * @return Collection<int, Category>
     */
    public static function navigation(): Collection
    {
        return Cache::rememberForever(self::NAVIGATION_CACHE_KEY, fn () => self::query()
            ->active()
            ->inMenu()
            ->whereNull('parent_id')
            ->ordered()
            ->with(['children' => fn ($query) => $query->active()->inMenu()->ordered()])
            ->get());
    }

    protected function casts(): array
    {
        return [
            'description' => SanitizedHtml::class,
            'is_active' => 'boolean',
            'show_in_menu' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('display_order');
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => match (true) {
            blank($this->image) => null,
            str($this->image)->startsWith(['http://', 'https://']) => $this->image,
            default => Storage::disk('public')->url($this->image),
        });
    }

    /**
     * Whether visitors can open the category: it is active, and so is its parent category if it has one.
     */
    public function isVisible(): bool
    {
        return $this->is_active && ($this->parent_id === null || $this->parent?->is_active === true);
    }

    /**
     * IDs of this category and its active sub-categories, whose products are listed together.
     *
     * @return list<int>
     */
    public function listingCategoryIds(): array
    {
        return [$this->id, ...$this->children()->active()->pluck('id')->all()];
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

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Active categories whose parent, if any, is active too.
     */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->active()->where(fn (Builder $query) => $query
            ->whereNull('parent_id')
            ->orWhereHas('parent', fn (Builder $query) => $query->active()));
    }

    #[Scope]
    protected function inMenu(Builder $query): void
    {
        $query->where('show_in_menu', true);
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('display_order')->orderBy('name');
    }
}
