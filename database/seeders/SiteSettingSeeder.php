<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Seed placeholder business settings. All values are editable from the admin panel.
     */
    public function run(): void
    {
        $settings = [
            'site_name' => 'Velo Printing & Gifting',
            'tagline' => 'Corporate gifting & printing, made memorable.',
            'hero_title' => 'Gifts that carry your brand',
            'hero_highlight' => 'further.',
            'hero_subtitle' => 'Customised gift sets, diaries, bags, pens, electronics and printing — branded with your logo and delivered in bulk.',
            'promo_title' => 'Corporate gifting, handled end to end',
            'promo_text' => 'From welcome kits for new joiners to festive hampers for clients, we source, brand and pack gifts that fit your budget and timeline.',
            'phone' => '+91 00000 00000',
            'whatsapp' => '+91 00000 00000',
            'email' => 'info@velo.example',
            'enquiry_email' => 'enquiry@velo.example',
            'enquiry_reply_to' => '',
            'enquiry_from_name' => '',
            'address' => 'Business address, City, State – 000000',
            'business_hours' => 'Mon – Sat, 10:00 AM – 7:00 PM',
            'facebook_url' => '',
            'instagram_url' => '',
            'linkedin_url' => '',
            'map_embed_url' => '',
            'currency_symbol' => '₹',
            'currency_code' => 'INR',
            'show_prices' => '1',
            'nav_category_limit' => '7',
            'price_ranges' => json_encode([
                ['min' => 0, 'max' => 250],
                ['min' => 250, 'max' => 500],
                ['min' => 500, 'max' => 1000],
                ['min' => 1000, 'max' => 2500],
                ['min' => 2500, 'max' => null],
            ]),
            'home_meta_title' => '',
            'home_meta_description' => '',
            'default_og_image' => '',
            'e_catalog_path' => '',
            'e_catalog_updated_at' => '',
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
