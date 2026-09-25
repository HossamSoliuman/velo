<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnquiryStatus;
use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    /**
     * List enquiries, newest first, with search and filters by status, date received and read state.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(EnquiryStatus::class)],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'unread' => ['nullable', 'boolean'],
        ]);

        $search = trim($filters['search'] ?? '');

        $enquiries = Enquiry::query()
            ->when($search !== '', fn ($query) => $query->search($search))
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->receivedBetween($filters['from'] ?? null, $filters['to'] ?? null)
            ->when($request->boolean('unread'), fn ($query) => $query->unread())
            ->latest()
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $statusCounts = Enquiry::query()->toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.enquiries.index', [
            'enquiries' => $enquiries,
            'search' => $search,
            'statusOptions' => collect(EnquiryStatus::cases())
                ->mapWithKeys(fn (EnquiryStatus $status) => [$status->value => $status->label().' ('.($statusCounts[$status->value] ?? 0).')'])
                ->all(),
        ]);
    }

    /**
     * Show the full enquiry. Opening it marks it as read.
     */
    public function show(Enquiry $enquiry): View
    {
        $enquiry->markAsRead();
        $enquiry->load('product:id,slug,is_active');

        return view('admin.enquiries.show', ['enquiry' => $enquiry]);
    }

    /**
     * Move the enquiry to another follow-up status.
     */
    public function update(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $enquiry->update($request->validate([
            'status' => ['required', Rule::enum(EnquiryStatus::class)],
        ]));

        return back()->with('status', "Enquiry from {$enquiry->name} marked as {$enquiry->status->label()}.");
    }

    public function destroy(Enquiry $enquiry): RedirectResponse
    {
        $enquiry->delete();

        return redirect()->route('admin.enquiries.index')->with('status', "Enquiry from {$enquiry->name} deleted.");
    }
}
