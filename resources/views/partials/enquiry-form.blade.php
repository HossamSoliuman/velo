{{--
    Enquiry form with the fields from the spec (section 32.1). Pass $product to attach a product.
    Submissions are handled by the "enquiries.store" route from the enquiries phase; until that route
    exists the form is shown but cannot be sent, and visitors are pointed to the phone and email instead.
--}}
@php
    use App\Models\SiteSetting;
    use Illuminate\Support\Facades\Route;

    $product ??= null;
    $canSubmit = Route::has('enquiries.store');
    $phone = SiteSetting::value('phone');
    $email = SiteSetting::value('email');

    $input = fn (string $name): string => 'block w-full rounded-xl border bg-white px-4 py-3 text-sm text-ink placeholder:text-slate-400 focus:ring-2 focus:ring-brand-500/20 focus:outline-none '
        .($errors->has($name) ? 'border-red-400 focus:border-red-500' : 'border-slate-300 focus:border-brand-500');
    $label = 'block text-sm font-semibold text-slate-700';
@endphp

<div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-brand-100 sm:p-8">
    <h2 class="text-2xl font-extrabold text-brand-800">{{ $product ? 'Enquire about this product' : 'Send us an enquiry' }}</h2>
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
                    <a href="{{ route('products.show', $product) }}" class="hover:text-brand-600">{{ $product->name }}</a>
                </p>
                <p class="mt-0.5 text-xs text-slate-500">SKU {{ $product->sku }} · Minimum quantity {{ number_format($product->minimum_qty) }}</p>
            </div>
            <a href="{{ route('contact') }}#enquiry" class="shrink-0 text-xs font-semibold text-slate-500 underline hover:text-brand-700">Remove</a>
        </div>
    @endif

    <form method="POST" action="{{ $canSubmit ? route('enquiries.store') : '#' }}" class="mt-6 grid gap-5 sm:grid-cols-2">
        @csrf
        @if ($product)
            <input type="hidden" name="product_id" value="{{ $product->id }}">
        @endif

        <div class="space-y-1.5">
            <label for="enquiry-name" class="{{ $label }}">Your name <span class="text-fan-magenta" aria-hidden="true">*</span></label>
            <input id="enquiry-name" name="name" type="text" value="{{ old('name') }}" required maxlength="100" autocomplete="name" class="{{ $input('name') }}">
            @error('name') <p class="text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-1.5">
            <label for="enquiry-company" class="{{ $label }}">Company</label>
            <input id="enquiry-company" name="company" type="text" value="{{ old('company') }}" maxlength="150" autocomplete="organization" class="{{ $input('company') }}">
            @error('company') <p class="text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-1.5">
            <label for="enquiry-email" class="{{ $label }}">Email <span class="text-fan-magenta" aria-hidden="true">*</span></label>
            <input id="enquiry-email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="{{ $input('email') }}">
            @error('email') <p class="text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-1.5">
            <label for="enquiry-mobile" class="{{ $label }}">Mobile number <span class="text-fan-magenta" aria-hidden="true">*</span></label>
            <input id="enquiry-mobile" name="mobile" type="tel" value="{{ old('mobile') }}" required maxlength="20" autocomplete="tel" class="{{ $input('mobile') }}">
            @error('mobile') <p class="text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-1.5 sm:col-span-2">
            <label for="enquiry-quantity" class="{{ $label }}">
                Required quantity
                @if ($product)
                    <span class="text-fan-magenta" aria-hidden="true">*</span>
                @endif
            </label>
            <input id="enquiry-quantity" name="quantity" type="number" inputmode="numeric"
                value="{{ old('quantity', $product?->minimum_qty) }}" min="{{ $product?->minimum_qty ?? 1 }}" max="10000000" @required($product) class="{{ $input('quantity') }} sm:w-48">
            @if ($product)
                <p class="text-xs text-slate-500">Minimum order for this product: {{ number_format($product->minimum_qty) }}.</p>
            @endif
            @error('quantity') <p class="text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-1.5 sm:col-span-2">
            <label for="enquiry-message" class="{{ $label }}">Message</label>
            <textarea id="enquiry-message" name="message" rows="4" maxlength="2000" placeholder="Branding, colours, delivery date, budget…" class="{{ $input('message') }}">{{ old('message') }}</textarea>
            @error('message') <p class="text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <button type="submit" @disabled(! $canSubmit)
                class="w-full rounded-full bg-fan-magenta px-8 py-3.5 text-sm font-bold text-white shadow-lg transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                Send Enquiry
            </button>
            @unless ($canSubmit)
                <p class="mt-3 text-sm text-slate-600">
                    Online enquiries open shortly.
                    @if ($phone || $email)
                        Until then, please
                        @if ($phone) call <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="font-semibold text-brand-600 hover:text-brand-800">{{ $phone }}</a> @endif
                        @if ($phone && $email) or @endif
                        @if ($email) email <a href="mailto:{{ $email }}" class="font-semibold text-brand-600 hover:text-brand-800">{{ $email }}</a>@endif.
                    @endif
                </p>
            @endunless
        </div>
    </form>
</div>
