@props(['field'])

{{-- A field's validation message. Rendered by the server after a normal post, and updated by the enquiryForm Alpine component after a background post. --}}
<p id="enquiry-{{ $field }}-error" class="text-xs font-semibold text-red-600" x-show="errors.{{ $field }}" x-text="errors.{{ $field }}?.[0]"
    @unless ($errors->has($field)) style="display: none" @endunless>{{ $errors->first($field) }}</p>
