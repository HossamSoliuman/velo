<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Enums\RobotsDirective;
use Illuminate\Validation\Rule;

trait ValidatesSeoFields
{
    /**
     * Rules for the SEO tab shared by categories, products and pages.
     *
     * @return array<string, array<mixed>>
     */
    protected function seoRules(): array
    {
        return [
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_og_image' => ['boolean'],
            'robots' => ['required', Rule::enum(RobotsDirective::class)],
        ];
    }
}
