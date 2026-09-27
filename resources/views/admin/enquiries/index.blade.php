@php
    $isFiltered = request()->hasAny(['search', 'status', 'from', 'to', 'unread']);
@endphp

<x-layouts.admin title="Enquiries">
    <p class="mb-6 max-w-2xl text-sm text-slate-600">
        Enquiries sent from product pages and the contact page. Each one is also emailed to the enquiry address in Settings.
        Unread enquiries are shown in bold.
    </p>

    <form method="GET" action="{{ route('admin.enquiries.index') }}" class="mb-6 grid grid-cols-2 gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 lg:grid-cols-[2fr_1.2fr_1fr_1fr_auto_auto] lg:items-end">
        <div class="col-span-2 lg:col-span-1">
            <x-admin.input name="search" type="search" label="Search" :value="$search" placeholder="Name, email, mobile, product or SKU" />
        </div>

        <div class="col-span-2 lg:col-span-1">
            <x-admin.select name="status" label="Status" :options="$statusOptions" :value="request('status')" placeholder="Any status" />
        </div>

        <x-admin.input name="from" type="date" label="Received from" :value="request('from')" />
        <x-admin.input name="to" type="date" label="Received to" :value="request('to')" />

        <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 lg:pb-2">
            <input type="checkbox" name="unread" value="1" @checked(request()->boolean('unread')) class="size-4 accent-brand-500">
            Unread only
        </label>

        <div class="flex items-center justify-end gap-2 lg:justify-start">
            <x-admin.button>Filter</x-admin.button>
            @if ($isFiltered)
                <a href="{{ route('admin.enquiries.index') }}" class="rounded-full px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Clear</a>
            @endif
        </div>
    </form>

    @if ($enquiries->isEmpty())
        <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-white px-6 py-10 text-center">
            <x-logo-mark class="mx-auto size-10 text-brand-200" />
            @if ($isFiltered)
                <p class="mt-3 text-sm font-semibold text-slate-700">No enquiries match these filters.</p>
                <a href="{{ route('admin.enquiries.index') }}" class="mt-2 inline-block text-sm font-semibold text-brand-600 hover:text-brand-800">Show all enquiries</a>
            @else
                <p class="mt-3 text-sm font-semibold text-slate-700">No enquiries yet.</p>
                <p class="mt-1 text-sm text-slate-500">When a visitor clicks Enquire Now or uses the contact form, their enquiry appears here.</p>
            @endif
        </div>
    @else
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="overflow-x-auto">
                <table class="data-table min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold tracking-wide text-slate-500 uppercase">
                        <tr>
                            <th scope="col" class="px-4 py-3">Received</th>
                            <th scope="col" class="px-4 py-3">Customer</th>
                            <th scope="col" class="px-4 py-3">Product</th>
                            <th scope="col" class="px-4 py-3 text-right">Qty</th>
                            <th scope="col" class="px-4 py-3">Status</th>
                            <th scope="col" class="px-4 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-slate-100 md:divide-y">
                        @foreach ($enquiries as $enquiry)
                            @php($unread = $enquiry->isUnread())
                            <tr @class(['bg-brand-50/60' => $unread])>
                                <td class="whitespace-nowrap md:px-4 md:py-3" data-label="Received">
                                    <div class="flex items-center gap-2">
                                        @if ($unread)
                                            <span class="size-2 shrink-0 rounded-full bg-fan-magenta" title="Unread"></span>
                                            <span class="sr-only">Unread,</span>
                                        @endif
                                        <div>
                                            <p @class(['text-ink', 'font-bold' => $unread])>{{ $enquiry->received_at->format('j M Y') }}</p>
                                            <p class="text-xs text-slate-500">{{ $enquiry->received_at->format('g:i A') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="md:max-w-64 md:px-4 md:py-3" data-primary>
                                    <a href="{{ route('admin.enquiries.show', $enquiry) }}" @class(['block truncate text-ink hover:text-brand-600', 'font-bold' => $unread, 'font-semibold' => ! $unread])>{{ $enquiry->name }}</a>
                                    <p class="truncate text-xs text-slate-500">{{ collect([$enquiry->company, $enquiry->email])->filter()->join(' · ') }}</p>
                                </td>
                                <td class="md:max-w-64 md:px-4 md:py-3" data-label="Product">
                                    @if ($enquiry->product_name !== null)
                                        <p class="font-semibold text-ink md:truncate">{{ $enquiry->product_name }}</p>
                                        <p class="text-xs text-slate-500">SKU {{ $enquiry->sku }}</p>
                                    @else
                                        <span class="text-xs font-semibold text-slate-400">General enquiry</span>
                                    @endif
                                </td>
                                <td class="text-slate-600 md:px-4 md:py-3 md:text-right" data-label="Qty">{{ $enquiry->quantity !== null ? number_format($enquiry->quantity) : '—' }}</td>
                                <td class="md:px-4 md:py-3" data-label="Status">
                                    <x-admin.enquiry-status :status="$enquiry->status" />
                                    @if ($enquiry->email_failed_at)
                                        <p class="mt-1 text-xs font-semibold text-red-600">Email not sent</p>
                                    @endif
                                </td>
                                <td class="md:px-4 md:py-3 md:text-right" data-actions>
                                    <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="font-semibold text-brand-600 hover:text-brand-800">View<span class="sr-only"> enquiry from {{ $enquiry->name }}</span></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-xs text-slate-500">{{ $enquiries->total() }} {{ str('enquiry')->plural($enquiries->total()) }}</p>
            </div>
        </div>

        <div class="mt-6">
            {{ $enquiries->links() }}
        </div>
    @endif
</x-layouts.admin>
