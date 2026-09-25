<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnquiryReadController extends Controller
{
    /**
     * Mark an enquiry as read, or as unread so it stands out again for follow-up.
     */
    public function update(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $request->validate(['read' => ['required', 'boolean']]);
        $read = $request->boolean('read');

        $enquiry->forceFill(['read_at' => $read ? ($enquiry->read_at ?? now()) : null])->save();

        return $read
            ? back()->with('status', "Enquiry from {$enquiry->name} marked as read.")
            : redirect()->route('admin.enquiries.index')->with('status', "Enquiry from {$enquiry->name} marked as unread.");
    }
}
