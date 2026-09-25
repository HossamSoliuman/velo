@php
    $ranges = old('price_ranges', $priceRanges);
@endphp

<x-layouts.admin title="Settings">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <x-admin.card title="General" description="The business name and tagline appear in the header, footer and browser tab.">
            <div class="grid gap-6 sm:grid-cols-2">
                <x-admin.input name="site_name" label="Business name" :value="$settings['site_name'] ?? ''" required />
                <x-admin.input name="tagline" label="Tagline" :value="$settings['tagline'] ?? ''" />
            </div>
        </x-admin.card>

        <x-admin.card title="Home page banner" description="The large heading at the top of the home page. The highlight is shown in yellow after the heading.">
            <div class="grid gap-6 sm:grid-cols-2">
                <x-admin.input name="hero_title" label="Heading" :value="$settings['hero_title'] ?? ''" required maxlength="120" />
                <x-admin.input name="hero_highlight" label="Highlighted words" :value="$settings['hero_highlight'] ?? ''" maxlength="60" />
            </div>
            <div class="mt-6">
                <x-admin.textarea name="hero_subtitle" label="Text below the heading" :value="$settings['hero_subtitle'] ?? ''" rows="2" maxlength="300" />
            </div>
        </x-admin.card>

        <x-admin.card title="Home page promotion" description="The blue corporate gifting section further down the home page. Leave blank to use the default wording.">
            <x-admin.input name="promo_title" label="Heading" :value="$settings['promo_title'] ?? ''" maxlength="120" />
            <div class="mt-6">
                <x-admin.textarea name="promo_text" label="Text" :value="$settings['promo_text'] ?? ''" rows="3" maxlength="400" />
            </div>
        </x-admin.card>

        <x-admin.card title="Contact details" description="Shown in the header, footer and on the contact page.">
            <div class="grid gap-6 sm:grid-cols-2">
                <x-admin.input name="phone" label="Phone" :value="$settings['phone'] ?? ''" />
                <x-admin.input name="whatsapp" label="WhatsApp number" :value="$settings['whatsapp'] ?? ''" />
                <x-admin.input name="email" type="email" label="Public email" :value="$settings['email'] ?? ''" />
                <x-admin.input name="business_hours" label="Business hours" :value="$settings['business_hours'] ?? ''" />
                <x-admin.input name="map_embed_url" type="url" label="Google Maps embed URL" :value="$settings['map_embed_url'] ?? ''" placeholder="https://www.google.com/maps/embed?pb=…"
                    hint="In Google Maps choose Share → Embed a map and copy the address inside src=&quot;…&quot;." />
            </div>
            <div class="mt-6">
                <x-admin.textarea name="address" label="Address" :value="$settings['address'] ?? ''" rows="2" />
            </div>
        </x-admin.card>

        <x-admin.card title="Enquiry emails" description="Every enquiry is saved under Enquiries and also emailed to the business.">
            <div class="grid gap-6 sm:grid-cols-2">
                <x-admin.input name="enquiry_email" type="email" label="Send enquiries to" :value="$settings['enquiry_email'] ?? ''" required
                    hint="The address that receives the enquiry emails." />
                <x-admin.input name="enquiry_reply_to" type="email" label="Reply-to address" :value="$settings['enquiry_reply_to'] ?? ''" placeholder="The customer's email"
                    hint="Leave blank so that replying to an enquiry email goes straight to the customer." />
                <x-admin.input name="enquiry_from_name" label="Sender name" :value="$settings['enquiry_from_name'] ?? ''" :placeholder="$settings['site_name'] ?? ''" maxlength="100"
                    hint="Leave blank to use the business name." />
                <x-admin.field label="Sender address">
                    <p class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-700 ring-1 ring-slate-200">{{ config('mail.from.address') }}</p>
                    <p class="text-xs text-slate-500">Set with the SMTP account in the server's mail configuration, so emails pass spam checks.</p>
                </x-admin.field>
            </div>
        </x-admin.card>

        <x-admin.card title="Social media" description="Leave blank to hide an icon.">
            <div class="grid gap-6 sm:grid-cols-3">
                <x-admin.input name="facebook_url" type="url" label="Facebook" :value="$settings['facebook_url'] ?? ''" placeholder="https://facebook.com/…" />
                <x-admin.input name="instagram_url" type="url" label="Instagram" :value="$settings['instagram_url'] ?? ''" placeholder="https://instagram.com/…" />
                <x-admin.input name="linkedin_url" type="url" label="LinkedIn" :value="$settings['linkedin_url'] ?? ''" placeholder="https://linkedin.com/company/…" />
            </div>
        </x-admin.card>

        <x-admin.card title="Catalogue" description="How prices and categories are presented on the website.">
            <div class="grid gap-6 sm:grid-cols-2">
                <x-admin.input name="currency_symbol" label="Currency symbol" :value="$settings['currency_symbol'] ?? '₹'" required maxlength="5" class="sm:w-32" />
                <x-admin.input name="nav_category_limit" type="number" label="Categories in the main menu bar" :value="$settings['nav_category_limit'] ?? 7" min="1" max="12" required class="sm:w-32"
                    hint="The rest are listed under All Categories." />
            </div>
            <x-admin.toggle name="show_prices" label="Show prices on the website" :checked="(bool) ($settings['show_prices'] ?? true)"
                description="When off, products show “Price on request” instead." class="mt-6" />

            <fieldset class="mt-8" x-data="{ ranges: {{ Js::from(array_values($ranges)) }} }">
                <legend class="text-sm font-semibold text-slate-700">Price ranges</legend>
                <p class="mt-1 text-xs text-slate-500">Shown in the Price Range menu. Leave the maximum blank for “and above”.</p>

                <div class="mt-3 space-y-3">
                    <template x-for="(range, index) in ranges" :key="index">
                        <div class="flex flex-wrap items-center gap-3">
                            <label class="sr-only" :for="`range-min-${index}`">Minimum price</label>
                            <input type="number" min="0" :id="`range-min-${index}`" :name="`price_ranges[${index}][min]`" x-model="range.min" placeholder="Min"
                                class="w-32 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                            <span class="text-slate-400">to</span>
                            <label class="sr-only" :for="`range-max-${index}`">Maximum price</label>
                            <input type="number" min="0" :id="`range-max-${index}`" :name="`price_ranges[${index}][max]`" x-model="range.max" placeholder="and above"
                                class="w-32 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                            <button type="button" x-on:click="ranges.splice(index, 1)" class="rounded-full px-3 py-1.5 text-sm font-semibold text-red-600 hover:bg-red-50">Remove</button>
                        </div>
                    </template>
                </div>

                <button type="button" x-on:click="ranges.push({ min: '', max: '' })" x-show="ranges.length < 10"
                    class="mt-3 rounded-full px-3 py-1.5 text-sm font-semibold text-brand-600 ring-1 ring-brand-200 hover:bg-brand-50">+ Add range</button>

                @foreach (collect($errors->get('price_ranges'))->merge(collect($errors->get('price_ranges.*'))->flatten())->unique() as $message)
                    <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                @endforeach
            </fieldset>
        </x-admin.card>

        @include('admin.partials.form-actions', ['cancelUrl' => route('admin.dashboard'), 'submitLabel' => 'Save settings'])
    </form>
</x-layouts.admin>
