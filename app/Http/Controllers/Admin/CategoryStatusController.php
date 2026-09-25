<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryStatusController extends Controller
{
    /**
     * Show or hide a category on the website or in the menu without opening the edit form.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($request->validate([
            'is_active' => ['sometimes', 'boolean'],
            'show_in_menu' => ['sometimes', 'boolean'],
        ]));

        return back()->with('status', "Category “{$category->name}” updated.");
    }
}
