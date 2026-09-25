<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Fills a blank slug from the model's name, adding a numeric suffix until it is unique.
 */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::saving(function (Model $model) {
            if (blank($model->slug)) {
                $model->slug = static::uniqueSlugFor((string) $model->name, $model->getKey());
            }
        });
    }

    /**
     * A URL slug for the given name that no other record of this model is using.
     */
    public static function uniqueSlugFor(string $name, ?int $ignoreId = null): string
    {
        $base = trim(Str::substr(Str::slug($name), 0, 240), '-') ?: 'item';
        $slug = $base;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
