<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsTxtController extends Controller
{
    /**
     * Crawler rules, served by the app so the sitemap address always uses the site's own domain.
     */
    public function __invoke(): Response
    {
        return response()
            ->view('robots', ['sitemapUrl' => route('sitemap')])
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
