@props(['status'])

@php
    use App\Enums\EnquiryStatus;
@endphp

<span {{ $attributes->class([
    'inline-block rounded-full px-2.5 py-1 text-xs font-bold whitespace-nowrap ring-1',
    match ($status) {
        EnquiryStatus::New => 'bg-pink-50 text-fan-magenta ring-pink-200',
        EnquiryStatus::Contacted => 'bg-sky-50 text-sky-700 ring-sky-200',
        EnquiryStatus::Quoted => 'bg-amber-50 text-amber-700 ring-amber-200',
        EnquiryStatus::Converted => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        EnquiryStatus::Closed => 'bg-slate-100 text-slate-500 ring-slate-200',
    },
]) }}>{{ $status->label() }}</span>
