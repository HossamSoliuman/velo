<?php

namespace App\Models\Concerns;

/**
 * Shared behaviour for records with meta title, meta description and robots fields.
 */
trait HasSeoFields
{
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
