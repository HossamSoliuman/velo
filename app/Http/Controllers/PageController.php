<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Show a content page such as About Us or the Privacy Policy. Each page's route names its slug.
     */
    public function show(Page $page): View
    {
        return view('pages.show', ['page' => $page]);
    }
}
