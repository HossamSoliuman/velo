{{--
    Enquiry form with the fields from the spec (section 32.1). Pass $product to attach a product, and
    $inModal when the form is shown in the product page's enquiry modal.
    With JavaScript the enquiryForm Alpine component sends the form in the background; without it the
    form posts normally and the server redirects back to the contact page with errors or a thank-you note.
--}}
@php
    use App\Http\Requests\StoreEnquiryRequest;

    $product ??= null;
    $inModal ??= false;
    $sentMessage = (string) session('enquiry_sent', '');
    $formError = $errors->first('enquiry') ?: $errors->first('product_id');

    $input = 'block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-ink placeholder:text-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none aria-invalid:border-red-400 aria-invalid:focus:border-red-500';
    $label = 'block text-sm font-semibold text-slate-700';
    $validation = fn (string $field): string => sprintf(
        'aria-describedby="enquiry-%1$s-error" aria-invalid="%2$s" x-bind:aria-invalid="errors.%1$s ? \'true\' : \'false\'"',
        $field,
        $errors->has($field) ? 'true' : 'false',
    );
@endphp

<div x-data="enquiryForm({{ Js::from((object) $errors->toArray()) }}, {{ Js::from($formError) }}, {{ Js::from($sentMessage) }})"
    @if ($inModal) x-on:open-enquiry.window="if (sent) startAgain()" @endif
    class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-brand-100 sm:p-8">

    {{-- Thank-you note --}}
    <div x-show="sent" x-ref="thanks" tabindex="-1" role="status" class="py-6 text-center focus:outline-none" @if ($sentMessage === '') style="display: none" @endif>
        <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-fan-lime/25 text-brand-700">
            <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
        </span>
        <h2 class="mt-4 text-2xl font-extrabold text-brand-800">Enquiry sent</h2>
        <p class="mx-auto mt-2 max-w-md text-slate-600" x-text="sentMessage">{{ $sentMessage }}</p>
        @if ($inModal)
            <button type="button" x-on:click="$dispatch('close-enquiry')" class="mt-6 rounded-full bg-brand-500 px-7 py-3 text-sm font-bold text-white hover:bg-brand-600">Close</button>
        @else
            <a href="{{ route('contact') }}#enquiry" x-on:click.prevent="startAgain()" class="mt-6 inline-block text-sm font-semibold text-brand-600 hover:text-brand-800">Send another enquiry</a>
        @endif
    </div>

    <div x-show="! sent" @if ($sentMessage !== '') style="display: none" @endif>
        <h2 id="enquiry-heading" class="text-2xl font-extrabold text-brand-800 @if ($inModal) pr-10 @endif">{{ $product ? 'Enquire about this product' : 'Send us an enquiry' }}</h2>
        <p class="mt-1 text-sm text-slate-600">Fields marked <span class="text-fan-magenta">*</span> are required.</p>

        @if ($product)
            <div class="mt-6 flex items-center gap-4 rounded-2xl bg-brand-50 p-4 ring-1 ring-brand-100">
                <div class="size-16 shrink-0 overflow-hidden rounded-xl bg-white ring-1 ring-brand-100">
                    @if ($product->primaryImage)
                        <img src="{{ $product->primaryImage->url }}" alt="" class="size-full object-cover">
                    @else
                        <x-logo-mark class="m-auto mt-3 size-10 text-brand-300 opacity-50" />
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate font-bold text-brand-800">
                        @if ($inModal)
                            {{ $product->name }}
                        @else
                            <a href="{{ route('products.show', $product) }}" class="hover:text-brand-600">{{ $product->name }}</a>
                        @endif
                    </p>
                    <p class="mt-0.5 text-xs text-slate-500">SKU {{ $product->sku }} · Minimum quantity {{ number_format($product->minimum_qty) }}</p>
                </div>
                @unless ($inModal)
                    <a href="{{ route('contact') }}#enquiry" class="shrink-0 text-xs font-semibold text-slate-500 underline hover:text-brand-700">Remove</a>
                @endunless
            </div>
        @endif

        <div x-show="formError" x-text="formError" role="alert" class="mt-6 rounded-xl border-l-4 border-red-500 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"
            @if ($formError === '') style="display: none" @endif>{{ $formError }}</div>

        <form method="POST" action="{{ route('enquiries.store') }}" x-on:submit.prevent="submit" class="mt-6 grid gap-5 sm:grid-cols-2">
            @csrf
            @if ($product)
                <input type="hidden" name="product_id" value="{{ $product->id }}">
            @endif

            <div class="absolute -left-[9999px]" aria-hidden="true">
                <label for="enquiry-{{ StoreEnquiryRequest::HONEYPOT_FIELD }}">Leave this field empty</label>
                <input id="enquiry-{{ StoreEnquiryRequest::HONEYPOT_FIELD }}" name="{{ StoreEnquiryRequest::HONEYPOT_FIELD }}" type="text" value="" tabindex="-1" autocomplete="off">
            </div>

            <div class="space-y-1.5">
                <label for="enquiry-name" class="{{ $label }}">Your name <span class="text-fan-magenta" aria-hidden="true">*</span></label>
                <input id="enquiry-name" name="name" type="text" value="{{ old('name') }}" required maxlength="100" autocomplete="name" class="{{ $input }}" {!! $validation('name') !!}>
                <x-enquiry-field-error field="name" />
            </div>

            <div class="space-y-1.5">
                <label for="enquiry-company" class="{{ $label }}">Company</label>
                <input id="enquiry-company" name="company" type="text" value="{{ old('company') }}" maxlength="150" autocomplete="organization" class="{{ $input }}" {!! $validation('company') !!}>
                <x-enquiry-field-error field="company" />
            </div>

            <div class="space-y-1.5">
                <label for="enquiry-email" class="{{ $label }}">Email <span class="text-fan-magenta" aria-hidden="true">*</span></label>
                <input id="enquiry-email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="{{ $input }}" {!! $validation('email') !!}>
                <x-enquiry-field-error field="email" />
            </div>

            <div class="space-y-1.5">
                <label for="enquiry-mobile" class="{{ $label }}">Mobile number <span class="text-fan-magenta" aria-hidden="true">*</span></label>
                <input id="enquiry-mobile" name="mobile" type="tel" value="{{ old('mobile') }}" required maxlength="20" autocomplete="tel" class="{{ $input }}" {!! $validation('mobile') !!}>
                <x-enquiry-field-error field="mobile" />
            </div>

            <div class="space-y-1.5 sm:col-span-2">
                <label for="enquiry-quantity" class="{{ $label }}">
                    Required quantity
                    @if ($product)
                        <span class="text-fan-magenta" aria-hidden="true">*</span>
                    @endif
                </label>
                <input id="enquiry-quantity" name="quantity" type="number" inputmode="numeric"
                    value="{{ old('quantity', $product?->minimum_qty) }}" min="{{ $product?->minimum_qty ?? 1 }}" max="10000000" @required($product) class="{{ $input }} sm:w-48" {!! $validation('quantity') !!}>
                @if ($product)
                    <p class="text-xs text-slate-500">Minimum order for this product: {{ number_format($product->minimum_qty) }}.</p>
                @endif
                <x-enquiry-field-error field="quantity" />
            </div>

            <div class="space-y-1.5 sm:col-span-2">
                <label for="enquiry-message" class="{{ $label }}">Message</label>
                <textarea id="enquiry-message" name="message" rows="4" maxlength="2000" placeholder="Branding, colours, delivery date, budget…" class="{{ $input }}" {!! $validation('message') !!}>{{ old('message') }}</textarea>
                <x-enquiry-field-error field="message" />
            </div>

            <div class="sm:col-span-2">
                <button type="submit" x-bind:disabled="sending"
                    class="w-full rounded-full bg-fan-magenta px-8 py-3.5 text-sm font-bold text-white shadow-lg transition hover:brightness-110 disabled:cursor-wait disabled:opacity-60 sm:w-auto">
                    <span x-text="sending ? 'Sending…' : 'Send Enquiry'">Send Enquiry</span>
                </button>
            </div>
        </form>
    </div>
</div>
