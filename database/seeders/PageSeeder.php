<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Placeholder content pages. The client supplies the final wording, which is edited from the admin panel.
     */
    public function run(): void
    {
        $pages = [
            'about-us' => ['About Us', 'Velo Printing & Gifting supplies customised corporate gifts and printing for businesses, events and teams. Replace this text with the approved company story from the admin panel.'],
            'privacy-policy' => ['Privacy Policy', 'This placeholder privacy policy will be replaced with the wording approved by Velo Printing & Gifting.'],
            'terms-and-conditions' => ['Terms and Conditions', 'These placeholder terms and conditions will be replaced with the wording approved by Velo Printing & Gifting.'],
        ];

        foreach ($pages as $slug => [$title, $content]) {
            Page::query()->firstOrCreate(['slug' => $slug], [
                'title' => $title,
                'content' => "<p>{$content}</p>",
            ]);
        }
    }
}
