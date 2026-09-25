{{-- Save bar pinned to the bottom of long admin forms. --}}
<div class="sticky bottom-0 z-10 -mx-4 mt-8 flex items-center justify-end gap-3 border-t border-slate-200 bg-white/95 px-4 py-3 backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
    <a href="{{ $cancelUrl }}" class="rounded-full px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Cancel</a>
    <x-admin.button>{{ $submitLabel ?? 'Save' }}</x-admin.button>
</div>
