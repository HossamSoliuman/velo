@php
    use App\Enums\EnquiryStatus;

    $mobileDigits = preg_replace('/\D/', '', $enquiry->mobile);
    $replySubject = 'Re: your enquiry'.($enquiry->product_name !== null ? ' about '.$enquiry->product_name : '');
    $contactLinks = [
        ['Email', $enquiry->email, 'mailto:'.$enquiry->email.'?subject='.rawurlencode($replySubject)],
        ['Mobile', $enquiry->mobile, 'tel:'.preg_replace('/[^\d+]/', '', $enquiry->mobile)],
    ];
@endphp

<x-layouts.admin :title="'Enquiry from '.$enquiry->name">
    <x-admin.breadcrumbs class="mb-6" :items="[
        ['Enquiries', route('admin.enquiries.index')],
        [$enquiry->name, null],
    ]" />

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between sm:p-6">
                    <div class="min-w-0">
                        <p class="text-xs font-bold tracking-wide text-slate-400 uppercase">Enquiry #{{ $enquiry->id }}</p>
                        <h2 class="mt-0.5 text-xl font-extrabold break-words text-brand-800">{{ $enquiry->name }}</h2>
                        @if ($enquiry->company)
                            <p class="text-sm font-semibold text-slate-600">{{ $enquiry->company }}</p>
                        @endif
                        <p class="mt-1 text-sm text-slate-500">
                            Received {{ $enquiry->received_at->format('j M Y, g:i A') }}
                            <span class="text-slate-400">({{ $enquiry->created_at->diffForHumans() }})</span>
                        </p>
                    </div>
                    <x-admin.enquiry-status :status="$enquiry->status" class="shrink-0 self-start" />
                </div>

                <dl class="grid border-t border-slate-100 sm:grid-cols-2">
                    @foreach ($contactLinks as [$label, $value, $href])
                        <div class="border-b border-slate-100 px-5 py-4 sm:px-6 sm:odd:border-r">
                            <dt class="text-xs font-bold tracking-wide text-slate-400 uppercase">{{ $label }}</dt>
                            <dd class="mt-1"><a href="{{ $href }}" class="font-semibold break-all text-brand-600 hover:text-brand-800">{{ $value }}</a></dd>
                        </div>
                    @endforeach
                </dl>

                <div class="flex flex-wrap gap-3 bg-slate-50/70 px-5 py-4 sm:px-6">
                    <x-admin.button :href="$contactLinks[0][2]">Reply by email</x-admin.button>
                    <x-admin.button :href="$contactLinks[1][2]" variant="secondary">Call</x-admin.button>
                    @if (strlen($mobileDigits) >= 10)
                        <x-admin.button :href="'https://wa.me/'.(strlen($mobileDigits) === 10 ? '91'.$mobileDigits : $mobileDigits)" variant="secondary" target="_blank" rel="noopener">WhatsApp</x-admin.button>
                    @endif
                </div>
            </section>

            <x-admin.card title="Requirement">
                <dl class="grid gap-5 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-bold tracking-wide text-slate-400 uppercase">Product</dt>
                        <dd class="mt-1">
                            @if ($enquiry->product_name !== null)
                                <p class="font-semibold text-ink">{{ $enquiry->product_name }}</p>
                                <p class="text-sm text-slate-500">SKU {{ $enquiry->sku }}</p>
                                <p class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm">
                                    @if ($enquiry->product)
                                        <a href="{{ route('admin.products.edit', $enquiry->product) }}" class="font-semibold text-brand-600 hover:text-brand-800">Edit product</a>
                                    @else
                                        <span class="text-slate-400">This product has since been deleted.</span>
                                    @endif
                                    @if ($enquiry->product_url && $enquiry->product?->is_active)
                                        <a href="{{ $enquiry->product_url }}" target="_blank" rel="noopener" class="font-semibold text-brand-600 hover:text-brand-800">View product page ↗</a>
                                    @endif
                                </p>
                            @else
                                <p class="font-semibold text-slate-500">General enquiry from the contact page</p>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold tracking-wide text-slate-400 uppercase">Quantity</dt>
                        <dd class="mt-1 text-2xl font-extrabold text-ink">{{ $enquiry->quantity !== null ? number_format($enquiry->quantity) : '—' }}</dd>
                    </div>
                </dl>

                <div class="mt-6 border-t border-slate-100 pt-5">
                    <h3 class="text-xs font-bold tracking-wide text-slate-400 uppercase">Message</h3>
                    @if (filled($enquiry->message))
                        <p class="mt-2 text-sm leading-relaxed break-words whitespace-pre-line text-slate-700">{{ $enquiry->message }}</p>
                    @else
                        <p class="mt-2 text-sm text-slate-400">No message.</p>
                    @endif
                </div>
            </x-admin.card>
        </div>

        <div class="space-y-6">
            <x-admin.card title="Follow-up status" description="Track where this enquiry is in your sales process.">
                <form method="POST" action="{{ route('admin.enquiries.update', $enquiry) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <x-admin.select name="status" label="Status" :options="EnquiryStatus::options()" :value="$enquiry->status->value" required />
                    <x-admin.button class="w-full">Update status</x-admin.button>
                </form>
            </x-admin.card>

            <x-admin.card title="Actions">
                <div class="grid gap-3">
                    <form method="POST" action="{{ route('admin.enquiries.read.update', $enquiry) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="read" value="0">
                        <x-admin.button variant="secondary" class="w-full">Mark as unread</x-admin.button>
                    </form>
                    <form method="POST" action="{{ route('admin.enquiries.destroy', $enquiry) }}"
                        onsubmit="return confirm({{ Js::from('Delete the enquiry from '.$enquiry->name.'? This cannot be undone.') }})">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full rounded-full px-5 py-2.5 text-sm font-bold text-red-600 hover:bg-red-50">Delete enquiry</button>
                    </form>
                </div>
            </x-admin.card>
        </div>
    </div>
</x-layouts.admin>
