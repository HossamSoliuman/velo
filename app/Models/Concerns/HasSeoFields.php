<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Shared behaviour for records with meta title, meta description, social sharing and robots fields.
 */
trait HasSeoFields
{
    /**
     * Whether search engines may index the record's page, according to its robots setting.
     */
    public function isIndexable(): bool
    {
        return ! Str::startsWith((string) $this->robots, 'noindex');
    }

    /**
     * Records whose robots setting allows indexing.
     */
    #[Scope]
    protected function indexable(Builder $query): void
    {
        $query->where(fn (Builder $query) => $query
            ->whereNull('robots')
            ->orWhere('robots', 'not like', 'noindex%'));
    }

    /**
     * Absolute URL of the uploaded social sharing image, if there is one.
     */
    public function ogImageUrl(): ?string
    {
        return match (true) {
            blank($this->og_image) => null,
            Str::startsWith($this->og_image, ['http://', 'https://']) => $this->og_image,
            default => url(Storage::disk('public')->url($this->og_image)),
        };
    }

    /**
     * Warnings for meta titles or descriptions that another record of the same type already uses.
     *
     * @return list<string>
     */
    public function seoWarnings(): array
    {
        $warnings = [];

        foreach (['meta_title' => 'meta title', 'meta_description' => 'meta description'] as $field => $label) {
            if (blank($this->{$field})) {
                continue;
            }

            $duplicate = static::query()->whereKeyNot($this->getKey())->where($field, $this->{$field})->first();

            if ($duplicate !== null) {
                $warnings[] = sprintf(
                    '“%s” already uses this %s. A unique %s helps search engines tell the pages apart.',
                    $duplicate->getAttribute('name') ?? $duplicate->getAttribute('title'),
                    $label,
                    $label,
                );
            }
        }

        return $warnings;
    }
}
