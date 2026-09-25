<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Drop price-range rows the admin left completely empty.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'price_ranges' => collect((array) $this->input('price_ranges', []))
                ->filter(fn (mixed $range) => is_array($range) && (filled($range['min'] ?? null) || filled($range['max'] ?? null)))
                ->values()
                ->all(),
        ]);
    }

    /**
     * Get the validation rules that apply to the request. Every key except price ranges maps to one site setting.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'hero_title' => ['required', 'string', 'max:120'],
            'hero_highlight' => ['nullable', 'string', 'max:60'],
            'hero_subtitle' => ['nullable', 'string', 'max:300'],
            'promo_title' => ['nullable', 'string', 'max:120'],
            'promo_text' => ['nullable', 'string', 'max:400'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'enquiry_email' => ['required', 'email', 'max:255'],
            'enquiry_reply_to' => ['nullable', 'email', 'max:255'],
            'enquiry_from_name' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'business_hours' => ['nullable', 'string', 'max:255'],
            'map_embed_url' => ['nullable', 'url:https', 'max:2000'],
            'facebook_url' => ['nullable', 'url:http,https', 'max:255'],
            'instagram_url' => ['nullable', 'url:http,https', 'max:255'],
            'linkedin_url' => ['nullable', 'url:http,https', 'max:255'],
            'currency_symbol' => ['required', 'string', 'max:5'],
            'show_prices' => ['required', 'boolean'],
            'nav_category_limit' => ['required', 'integer', 'min:1', 'max:12'],
            'price_ranges' => ['array', 'max:10'],
            'price_ranges.*.min' => ['required', 'integer', 'min:0'],
            'price_ranges.*.max' => ['nullable', 'integer', 'gt:price_ranges.*.min'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'enquiry_email' => 'enquiry email',
            'enquiry_reply_to' => 'reply-to address',
            'enquiry_from_name' => 'sender name',
            'map_embed_url' => 'map embed URL',
            'facebook_url' => 'Facebook URL',
            'instagram_url' => 'Instagram URL',
            'linkedin_url' => 'LinkedIn URL',
            'nav_category_limit' => 'menu category limit',
            'price_ranges.*.min' => 'minimum price',
            'price_ranges.*.max' => 'maximum price',
        ];
    }
}
