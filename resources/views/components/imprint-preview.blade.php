@php
    $finishes = [
        'engraved' => ['Laser engraved', 'bg-brand-950 shadow-[inset_0_1px_3px_rgb(0_0_0/0.7)]'],
        'white' => ['Printed in white', 'bg-white'],
        'yellow' => ['Printed in yellow', 'bg-fan-yellow'],
        'cyan' => ['Printed in cyan', 'bg-fan-cyan'],
        'magenta' => ['Printed in magenta', 'bg-fan-magenta'],
    ];
@endphp

{{-- A diary and pen that carry the company name the visitor types, in the finish they pick. --}}
<div x-data="imprintPreview" {{ $attributes->class('mx-auto w-full max-w-[26rem]') }}>
    <div class="gift-set" data-finish="engraved" :data-finish="finish" aria-hidden="true">
        <div class="gift-diary">
            <span class="gift-diary-ribbon"></span>
            <div class="gift-diary-cover">
                <p class="gift-imprint gift-diary-imprint" :style="{ fontSize: diarySize }" x-text="imprint">Your brand</p>
            </div>
            <span class="gift-diary-band"></span>
        </div>

        <div class="gift-pen">
            <span class="gift-pen-cap"></span>
            <span class="gift-pen-clip"></span>
            <span class="gift-pen-ring"></span>
            <span class="gift-pen-barrel"></span>
            <span class="gift-pen-tip"></span>
            <p class="gift-imprint gift-pen-imprint" :style="{ fontSize: penSize }" x-text="imprint">Your brand</p>
        </div>
    </div>

    <div x-cloak class="mt-8 space-y-5">
        <div>
            <label for="imprint-name" class="text-sm font-semibold text-white/85">Your company name</label>
            <input id="imprint-name" type="text" x-model="name" maxlength="30" autocomplete="organization" placeholder="Type it to see it on the diary and pen"
                class="mt-2 w-full rounded-full bg-brand-600/60 px-5 py-3 text-base font-semibold text-white ring-1 ring-white/30 transition placeholder:font-normal placeholder:text-white/55 focus:bg-brand-600 focus:ring-2 focus:ring-white focus:outline-none sm:text-sm">
        </div>

        <fieldset class="flex items-center gap-3">
            <legend class="sr-only">Branding</legend>
            @foreach ($finishes as $value => [$label, $swatch])
                <label class="cursor-pointer">
                    <input type="radio" name="imprint-finish" value="{{ $value }}" x-model="finish" class="peer sr-only">
                    <span class="block size-7 rounded-full ring-1 ring-white/40 transition {{ $swatch }} peer-checked:ring-2 peer-checked:ring-white peer-checked:ring-offset-2 peer-checked:ring-offset-brand-500 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-4 peer-focus-visible:outline-white"></span>
                    <span class="sr-only">{{ $label }}</span>
                </label>
            @endforeach
            @foreach ($finishes as $value => [$label])
                <span x-show="finish === '{{ $value }}'" class="ml-2 text-sm font-semibold text-white/85" aria-hidden="true">{{ $label }}</span>
            @endforeach
        </fieldset>
    </div>
</div>
