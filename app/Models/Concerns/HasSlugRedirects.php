<?php

namespace App\Models\Concerns;

use App\Models\SlugRedirect;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Remembers previous slugs so old URLs can permanently redirect to the record's current URL.
 */
trait HasSlugRedirects
{
    public static function bootHasSlugRedirects(): void
    {
        static::updated(function (Model $model) {
            if ($model->wasChanged('slug') && filled($model->getOriginal('slug'))) {
                SlugRedirect::query()->updateOrCreate(
                    ['redirectable_type' => $model->getMorphClass(), 'old_slug' => $model->getOriginal('slug')],
                    ['redirectable_id' => $model->getKey()],
                );
            }
        });

        // A live slug always wins over a redirect that used to point elsewhere.
        static::saved(function (Model $model) {
            if ($model->wasRecentlyCreated || $model->wasChanged('slug')) {
                SlugRedirect::query()
                    ->where('redirectable_type', $model->getMorphClass())
                    ->where('old_slug', $model->slug)
                    ->delete();
            }
        });

        static::deleted(fn (Model $model) => $model->slugRedirects()->delete());
    }

    /**
     * @return MorphMany<SlugRedirect, $this>
     */
    public function slugRedirects(): MorphMany
    {
        return $this->morphMany(SlugRedirect::class, 'redirectable');
    }

    /**
     * The record that previously used the given slug, if any.
     */
    public static function findByPreviousSlug(string $slug): ?static
    {
        $redirect = SlugRedirect::query()
            ->where('redirectable_type', (new static)->getMorphClass())
            ->where('old_slug', $slug)
            ->first();

        return $redirect?->redirectable;
    }
}
